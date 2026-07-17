import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AnalyticsIdentityStore, AnalyticsRouteObserver, AnalyticsTracker, AnalyticsTransport, shouldSkip } from '../analytics-tracker';

describe('AnalyticsIdentityStore', () => {
  beforeEach(() => { localStorage.clear(); sessionStorage.clear(); });

  it('reuses valid visitor and session IDs and rotates expired visitors', () => {
    const first = new AnalyticsIdentityStore(window, () => 1_000).get();
    const reused = new AnalyticsIdentityStore(window, () => 2_000).get();
    expect(reused).toEqual(first);
    localStorage.setItem('glitter-analytics:visitor-id', JSON.stringify({ id: first.visitorId, createdAt: 0 }));
    const rotated = new AnalyticsIdentityStore(window, () => 91 * 86_400_000).get();
    expect(rotated.visitorId).not.toBe(first.visitorId);
    expect(rotated.sessionId).toBe(first.sessionId);
  });

  it('falls back to memory when storage throws', () => {
    const broken = { getItem: () => { throw new Error('denied'); }, setItem: () => { throw new Error('denied'); } } as unknown as Storage;
    const fake = { ...window, localStorage: broken, sessionStorage: broken } as Window;
    const ids = new AnalyticsIdentityStore(fake).get();
    expect(ids.visitorId).toMatch(/^[a-f0-9-]{36}$/);
    expect(ids.sessionId).toMatch(/^[a-f0-9-]{36}$/);
  });
});

describe('AnalyticsTransport', () => {
  it('uses beacon and falls back once to keepalive fetch', async () => {
    const beacon = vi.fn().mockReturnValue(false);
    const fetcher = vi.fn().mockResolvedValue(new Response(null, { status: 202 }));
    const transport = new AnalyticsTransport(beacon, fetcher);
    await transport.send({ path: '/a', source: 'initial', visitor_id: 'x', session_id: 'y' });
    expect(beacon).toHaveBeenCalledOnce();
    expect(fetcher).toHaveBeenCalledWith('/api/modules/glitter-analytics/pageviews', expect.objectContaining({ method: 'POST', keepalive: true, credentials: 'same-origin' }));
  });
});

describe('AnalyticsRouteObserver', () => {
  it('observes pathname changes and ignores query/hash-only changes', () => {
    const listener = vi.fn();
    const observer = new AnalyticsRouteObserver(window, listener);
    observer.start();
    history.pushState({}, '', '/route-a?x=1');
    history.replaceState({}, '', '/route-a?x=2#hash');
    history.pushState({}, '', '/route-b');
    expect(listener.mock.calls.map((call) => call[0])).toEqual(['/route-a', '/route-b']);
    observer.stop();
  });
});

describe('AnalyticsTracker', () => {
  it('sends initial once and skips privacy and excluded paths', async () => {
    history.replaceState({}, '', '/public');
    const send = vi.fn().mockResolvedValue(undefined);
    const tracker = new AnalyticsTracker(window, { send } as unknown as AnalyticsTransport, { visitorId: 'v', sessionId: 's' });
    tracker.start(); tracker.start();
    await Promise.resolve();
    expect(send).toHaveBeenCalledOnce();

    history.replaceState({}, '', '/admin/users');
    tracker.onPathChange('/admin/users');
    expect(send).toHaveBeenCalledOnce();
    tracker.stop();
  });
});

describe('privacy precheck', () => {
  it('skips DNT, GPC, admin, API, build, static assets and allows public paths', () => {
    Object.defineProperty(navigator, 'doNotTrack', { value: '1', configurable: true });
    expect(shouldSkip(window, '/public')).toBe(true);
    Object.defineProperty(navigator, 'doNotTrack', { value: null, configurable: true });
    Object.defineProperty(navigator, 'globalPrivacyControl', { value: true, configurable: true });
    expect(shouldSkip(window, '/public')).toBe(true);
    Object.defineProperty(navigator, 'globalPrivacyControl', { value: false, configurable: true });
    for (const path of ['/admin', '/api/x', '/build/app.js', '/images/a.webp']) expect(shouldSkip(window, path)).toBe(true);
    expect(shouldSkip(window, '/boards/free')).toBe(false);
  });
});

describe('browser diagnostics', () => {
  it('prints structured stages only when debug mode is enabled', async () => {
    history.replaceState({}, '', '/public?analytics_debug=1');
    const debug = vi.spyOn(console, 'debug').mockImplementation(() => undefined);
    const tracker = new AnalyticsTracker(window, { send: vi.fn().mockResolvedValue(undefined) } as unknown as AnalyticsTransport, { visitorId: 'visitor-raw', sessionId: 'session-raw' });
    tracker.start();
    await Promise.resolve();
    const output = JSON.stringify(debug.mock.calls);
    expect(output).toContain('[glitter-analytics] init');
    expect(output).not.toContain('visitor-raw');
    expect(output).not.toContain('session-raw');
    tracker.stop();
    debug.mockRestore();
  });
});

describe('entry bootstrap and visibility', () => {
  it('starts the tracker exactly once when the entry is imported', async () => {
    const start = vi.fn();
    vi.resetModules();
    vi.doMock('../analytics-tracker', () => ({ AnalyticsTracker: class { start(): void { start(); } } }));
    await import('../index');
    expect(start).toHaveBeenCalledOnce();
    vi.doUnmock('../analytics-tracker');
  });

  it('sends initial once after an initially hidden document becomes visible', async () => {
    history.replaceState({}, '', '/visible-later');
    Object.defineProperty(document, 'visibilityState', { value: 'hidden', configurable: true });
    const send = vi.fn().mockResolvedValue(undefined);
    const tracker = new AnalyticsTracker(window, { send } as unknown as AnalyticsTransport, { visitorId: 'v', sessionId: 's' });
    tracker.start();
    expect(send).not.toHaveBeenCalled();
    Object.defineProperty(document, 'visibilityState', { value: 'visible', configurable: true });
    document.dispatchEvent(new Event('visibilitychange'));
    document.dispatchEvent(new Event('visibilitychange'));
    await Promise.resolve();
    expect(send).toHaveBeenCalledOnce();
    tracker.stop();
  });
});


describe('Phase 5.1 diagnostics and transport regression', () => {
  beforeEach(() => {
    sessionStorage.removeItem('glitter_analytics_debug');
    history.replaceState({}, '', '/phase-5?analytics_debug=0');
    Object.defineProperty(document, 'visibilityState', { value: 'visible', configurable: true });
  });

  it('does not log when diagnostics are disabled', async () => {
    const debug = vi.spyOn(console, 'debug').mockImplementation(() => undefined);
    const transport = new AnalyticsTransport(vi.fn().mockReturnValue(true), vi.fn(), window);
    await transport.send({ path: '/phase-5', source: 'initial', visitor_id: 'raw-visitor', session_id: 'raw-session' });
    expect(debug).not.toHaveBeenCalled();
    debug.mockRestore();
  });

  it('logs beacon queue acceptance without raw identifiers and never falls back when queued', async () => {
    history.replaceState({}, '', '/phase-5?analytics_debug=1');
    const debug = vi.spyOn(console, 'debug').mockImplementation(() => undefined);
    const beacon = vi.fn().mockReturnValue(true);
    const fetcher = vi.fn();
    await new AnalyticsTransport(beacon, fetcher, window).send({ path: '/phase-5', source: 'initial', visitor_id: 'raw-visitor', session_id: 'raw-session' });
    const output = JSON.stringify(debug.mock.calls);
    expect(output).toContain('transport:beacon');
    expect(output).toContain('queued');
    expect(output).toContain('visitor_id_present');
    expect(output).not.toContain('raw-visitor');
    expect(output).not.toContain('raw-session');
    expect(fetcher).not.toHaveBeenCalled();
    debug.mockRestore();
  });

  it('uses fetch fallback and reports its HTTP response when beacon returns false', async () => {
    history.replaceState({}, '', '/phase-5?analytics_debug=1');
    const debug = vi.spyOn(console, 'debug').mockImplementation(() => undefined);
    const fetcher = vi.fn().mockResolvedValue(new Response(null, { status: 202 }));
    await new AnalyticsTransport(vi.fn().mockReturnValue(false), fetcher, window).send({ path: '/phase-5', source: 'initial' });
    expect(fetcher).toHaveBeenCalledOnce();
    expect(debug.mock.calls.some(([message, details]) => message === '[glitter-analytics] transport:beacon' && details.queued === false)).toBe(true);
    expect(debug.mock.calls.some(([message, details]) => message === '[glitter-analytics] transport:fetch' && details.status === 202)).toBe(true);
    debug.mockRestore();
  });

  it('survives a beacon exception and falls back once', async () => {
    history.replaceState({}, '', '/phase-5?analytics_debug=1');
    const debug = vi.spyOn(console, 'debug').mockImplementation(() => undefined);
    const fetcher = vi.fn().mockResolvedValue(new Response(null, { status: 202 }));
    const beacon = vi.fn().mockImplementation(() => { throw new Error('blocked'); });
    await expect(new AnalyticsTransport(beacon, fetcher, window).send({ path: '/phase-5', source: 'initial' })).resolves.toBeUndefined();
    expect(fetcher).toHaveBeenCalledOnce();
    expect(debug.mock.calls.some(([message, details]) => message === '[glitter-analytics] transport:error' && details.transport === 'beacon')).toBe(true);
    debug.mockRestore();
  });

  it('logs readiness, SPA navigation, and duplicate skips without extra query/hash sends', async () => {
    history.replaceState({}, '', '/route-a?analytics_debug=1');
    const debug = vi.spyOn(console, 'debug').mockImplementation(() => undefined);
    const send = vi.fn().mockResolvedValue(undefined);
    const tracker = new AnalyticsTracker(window, { send } as unknown as AnalyticsTransport, { visitorId: 'raw-visitor', sessionId: 'raw-session' });
    tracker.start();
    history.pushState({}, '', '/route-a?x=1');
    history.replaceState({}, '', '/route-a#hash');
    tracker.onPathChange('/route-a');
    history.pushState({}, '', '/route-b');
    history.pushState({}, '', '/route-a');
    await Promise.resolve();
    expect(send.mock.calls.map(([payload]) => [payload.path, payload.source])).toEqual([
      ['/route-a', 'initial'], ['/route-b', 'spa'], ['/route-a', 'spa'],
    ]);
    const output = JSON.stringify(debug.mock.calls);
    expect(output).toContain('[glitter-analytics] initial:ready');
    expect(output).toContain('[glitter-analytics] spa:navigation');
    expect(output).toContain('duplicate_path');
    expect(output).not.toContain('raw-visitor');
    expect(output).not.toContain('raw-session');
    tracker.stop();
    debug.mockRestore();
  });

  it('does not call transport for an excluded path', () => {
    history.replaceState({}, '', '/admin/analytics?analytics_debug=1');
    const send = vi.fn();
    const tracker = new AnalyticsTracker(window, { send } as unknown as AnalyticsTransport, { visitorId: 'v', sessionId: 's' });
    tracker.start();
    expect(send).not.toHaveBeenCalled();
    tracker.stop();
  });
});
