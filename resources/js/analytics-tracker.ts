export type PageViewSource = 'initial' | 'spa';
export interface PageViewPayload { path: string; source: PageViewSource; referrer?: string; locale?: string; visitor_id?: string; session_id?: string }
export interface AnalyticsIds { visitorId: string; sessionId: string }

const VISITOR_KEY = 'glitter-analytics:visitor-id';
const SESSION_KEY = 'glitter-analytics:session-id';
const VISITOR_TTL = 90 * 24 * 60 * 60 * 1000;
const DEBUG_KEY = 'glitter_analytics_debug';
type DiagnosticDetails = Record<string, string | number | boolean | undefined>;

function debugEnabled(target: Window): boolean {
  const value = new URLSearchParams(target.location.search).get('analytics_debug');
  try {
    if (value === '1') target.sessionStorage.setItem(DEBUG_KEY, '1');
    if (value === '0') target.sessionStorage.removeItem(DEBUG_KEY);
    return value === '1' || (value !== '0' && target.sessionStorage.getItem(DEBUG_KEY) === '1');
  } catch { return value === '1'; }
}
function diagnostic(target: Window, event: string, details: DiagnosticDetails = {}): void {
  if (debugEnabled(target)) target.console.debug(`[glitter-analytics] ${event}`, details);
}

function randomId(target: Window): string {
  if (typeof target.crypto?.randomUUID === 'function') return target.crypto.randomUUID();
  const bytes = new Uint8Array(16);
  target.crypto.getRandomValues(bytes);
  bytes[6] = (bytes[6] & 0x0f) | 0x40;
  bytes[8] = (bytes[8] & 0x3f) | 0x80;
  const hex = [...bytes].map((value) => value.toString(16).padStart(2, '0')).join('');
  return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}

export class AnalyticsIdentityStore {
  private memoryVisitor?: string;
  private memorySession?: string;
  constructor(private target: Window = window, private now: () => number = Date.now) {}

  get(): AnalyticsIds {
    return { visitorId: this.visitor(), sessionId: this.session() };
  }

  private visitor(): string {
    try {
      const parsed = JSON.parse(this.target.localStorage.getItem(VISITOR_KEY) ?? 'null') as { id?: string; createdAt?: number } | null;
      if (parsed?.id && typeof parsed.createdAt === 'number' && this.now() - parsed.createdAt < VISITOR_TTL && this.now() >= parsed.createdAt) return parsed.id;
      const id = randomId(this.target);
      this.target.localStorage.setItem(VISITOR_KEY, JSON.stringify({ id, createdAt: this.now() }));
      return id;
    } catch {
      return this.memoryVisitor ??= randomId(this.target);
    }
  }

  private session(): string {
    try {
      const existing = this.target.sessionStorage.getItem(SESSION_KEY);
      if (existing) return existing;
      const id = randomId(this.target);
      this.target.sessionStorage.setItem(SESSION_KEY, id);
      return id;
    } catch {
      return this.memorySession ??= randomId(this.target);
    }
  }
}

export class AnalyticsTransport {
  constructor(
    private beacon: (url: string, data?: BodyInit | null) => boolean = navigator.sendBeacon.bind(navigator),
    private fetcher: typeof fetch = window.fetch.bind(window),
    private target: Window = window,
  ) {}

  async send(payload: PageViewPayload): Promise<void> {
    const body = JSON.stringify(payload);
    const summary = { path: payload.path, source: payload.source, locale: payload.locale, visitor_id_present: Boolean(payload.visitor_id), session_id_present: Boolean(payload.session_id) };
    try {
      const queued = this.beacon('/api/modules/glitter-analytics/pageviews', new Blob([body], { type: 'application/json' }));
      diagnostic(this.target, 'transport:beacon', { ...summary, queued });
      if (queued) return;
    } catch {
      diagnostic(this.target, 'transport:error', { transport: 'beacon', reason: 'exception' });
    }
    try {
      diagnostic(this.target, 'transport:fetch', { ...summary, phase: 'request' });
      const response = await this.fetcher('/api/modules/glitter-analytics/pageviews', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body, credentials: 'same-origin', keepalive: true });
      diagnostic(this.target, 'transport:fetch', { phase: 'response', status: response.status, ok: response.ok });
    } catch {
      diagnostic(this.target, 'transport:error', { transport: 'fetch', reason: 'request_failed' });
    }
  }
}

type RouteListener = (path: string) => void;
interface ObserverState { listeners: Set<RouteListener>; pushState: History['pushState']; replaceState: History['replaceState']; pop: () => void; lastPath: string }
const OBSERVER_KEY = Symbol.for('glitter-analytics:route-observer');

export class AnalyticsRouteObserver {
  private started = false;
  constructor(private target: Window, private listener: RouteListener) {}

  start(): void {
    if (this.started) return;
    this.started = true;
    const holder = this.target as Window & { [OBSERVER_KEY]?: ObserverState };
    let state = holder[OBSERVER_KEY];
    if (!state) {
      const notify = () => {
        const next = this.target.location.pathname;
        const current = holder[OBSERVER_KEY];
        if (!current || next === current.lastPath) return;
        current.lastPath = next;
        current.listeners.forEach((callback) => callback(next));
      };
      const pushState = this.target.history.pushState;
      const replaceState = this.target.history.replaceState;
      const pop = () => notify();
      state = { listeners: new Set(), pushState, replaceState, pop, lastPath: this.target.location.pathname };
      holder[OBSERVER_KEY] = state;
      this.target.history.pushState = function (...args) { const result = pushState.apply(this, args); notify(); return result; };
      this.target.history.replaceState = function (...args) { const result = replaceState.apply(this, args); notify(); return result; };
      this.target.addEventListener('popstate', pop);
    }
    state.listeners.add(this.listener);
  }

  stop(): void {
    if (!this.started) return;
    this.started = false;
    const holder = this.target as Window & { [OBSERVER_KEY]?: ObserverState };
    const state = holder[OBSERVER_KEY];
    if (!state) return;
    state.listeners.delete(this.listener);
    if (state.listeners.size === 0) {
      this.target.history.pushState = state.pushState;
      this.target.history.replaceState = state.replaceState;
      this.target.removeEventListener('popstate', state.pop);
      delete holder[OBSERVER_KEY];
    }
  }
}

const STATIC_EXTENSION = /\.(?:js|css|map|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot)$/i;
export type EligibilityReason = 'eligible' | 'iframe' | 'dnt' | 'gpc' | 'admin_path' | 'api_path' | 'build_path' | 'static_asset';
export function eligibility(target: Window, path: string): { eligible: boolean; reason: EligibilityReason } {
  try { if (target.top !== target.self) return { eligible: false, reason: 'iframe' }; } catch { return { eligible: false, reason: 'iframe' }; }
  const privacy = target.navigator as Navigator & { globalPrivacyControl?: boolean };
  if (privacy.doNotTrack === '1' || (target as Window & { doNotTrack?: string }).doNotTrack === '1') return { eligible: false, reason: 'dnt' };
  if (privacy.globalPrivacyControl === true) return { eligible: false, reason: 'gpc' };
  if (path === '/admin' || path.startsWith('/admin/')) return { eligible: false, reason: 'admin_path' };
  if (path === '/api' || path.startsWith('/api/')) return { eligible: false, reason: 'api_path' };
  if (path === '/build' || path.startsWith('/build/')) return { eligible: false, reason: 'build_path' };
  if (STATIC_EXTENSION.test(path)) return { eligible: false, reason: 'static_asset' };
  return { eligible: true, reason: 'eligible' };
}
export function shouldSkip(target: Window, path: string): boolean { return !eligibility(target, path).eligible; }

export class AnalyticsTracker {
  private observer?: AnalyticsRouteObserver;
  private started = false;
  private lastSentPath?: string;
  private visibilityListener?: () => void;
  constructor(private target: Window = window, private transport = new AnalyticsTransport(), private ids = new AnalyticsIdentityStore(target).get()) {}

  start(): void {
    if (this.started) { diagnostic(this.target, 'skipped', { reason: 'already_started' }); return; }
    this.started = true;
    const path = this.target.location.pathname;
    diagnostic(this.target, 'init', { path, visibility: this.target.document.visibilityState });
    const result = eligibility(this.target, path);
    diagnostic(this.target, 'eligibility', { eligible: result.eligible, reason: result.reason, path });
    if (!result.eligible) { diagnostic(this.target, 'skipped', { reason: result.reason, path }); return; }
    const documentWithPrerender = this.target.document as Document & { prerendering?: boolean };
    if (documentWithPrerender.prerendering) {
      diagnostic(this.target, 'initial:deferred', { reason: 'prerendering', path });
      this.target.document.addEventListener('prerenderingchange', () => this.prepareInitial(), { once: true });
    } else if (this.target.document.visibilityState !== 'hidden') {
      this.prepareInitial();
    } else {
      diagnostic(this.target, 'initial:deferred', { reason: 'visibility_hidden', path });
      this.visibilityListener = () => {
        if (this.target.document.visibilityState === 'hidden') return;
        this.target.document.removeEventListener('visibilitychange', this.visibilityListener!);
        this.visibilityListener = undefined;
        this.prepareInitial();
      };
      this.target.document.addEventListener('visibilitychange', this.visibilityListener);
    }
    this.observer = new AnalyticsRouteObserver(this.target, (nextPath) => this.onPathChange(nextPath));
    this.observer.start();
  }

  stop(): void {
    this.observer?.stop();
    if (this.visibilityListener) this.target.document.removeEventListener('visibilitychange', this.visibilityListener);
    this.visibilityListener = undefined;
    this.started = false;
  }
  onPathChange(path: string): void {
    diagnostic(this.target, 'spa:navigation', { from: this.lastSentPath, to: path });
    this.send(path, 'spa');
  }
  private prepareInitial(): void {
    const path = this.target.location.pathname;
    diagnostic(this.target, 'initial:ready', { path });
    this.send(path, 'initial');
  }
  private send(path: string, source: PageViewSource): void {
    if (path === this.lastSentPath) { diagnostic(this.target, 'skipped', { reason: 'duplicate_path', path, source }); return; }
    const result = eligibility(this.target, path);
    if (!result.eligible) { diagnostic(this.target, 'skipped', { reason: result.reason, path, source }); return; }
    this.lastSentPath = path;
    const payload = { path, source, referrer: this.target.document.referrer || undefined, locale: this.target.document.documentElement.lang || undefined, visitor_id: this.ids.visitorId, session_id: this.ids.sessionId };
    void this.transport.send(payload).catch(() => diagnostic(this.target, 'transport:error', { transport: 'unknown', reason: 'unhandled_rejection_prevented' }));
  }
}
