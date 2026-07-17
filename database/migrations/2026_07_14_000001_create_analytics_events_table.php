<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analytics_events')) {
            return;
        }

        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->comment('사이트 방문 원본 이벤트');
            $table->id()->comment('방문 이벤트 ID');
            $table->string('event_type', 32)->comment('이벤트 유형 (page_view: 페이지 조회)');
            $table->string('source', 16)->comment('이벤트 발생 경로 (initial: 최초 진입, spa: 화면 내부 이동)');
            $table->timestamp('occurred_at')->comment('이벤트 발생 UTC 시각');
            $table->string('path', 512)->comment('쿼리와 fragment를 제외한 요청 경로');
            $table->string('route_name', 191)->nullable()->comment('애플리케이션 라우트 이름');
            $table->string('referrer_host', 255)->nullable()->comment('userinfo를 제외한 이전 페이지 호스트');
            $table->string('referrer_path', 512)->nullable()->comment('쿼리와 fragment를 제외한 이전 페이지 경로');
            $table->unsignedBigInteger('user_id')->nullable()->comment('연결된 사용자 ID (외래키 제약 없이 명시적으로 참조 해제)');
            $table->char('visitor_hash', 64)->nullable()->comment('익명 방문자 HMAC-SHA256 식별값');
            $table->char('session_hash', 64)->nullable()->comment('익명 방문 세션 HMAC-SHA256 식별값');
            $table->char('ip_hash', 64)->nullable()->comment('원문을 저장하지 않는 IP HMAC-SHA256 식별값');
            $table->string('locale', 12)->nullable()->comment('이벤트 발생 시 적용 로케일');
            $table->timestamp('created_at')->nullable()->comment('레코드 생성 시각');
            $table->timestamp('updated_at')->nullable()->comment('사용자 참조 해제 등 최종 변경 시각');

            $table->index('occurred_at', 'analytics_events_occurred_at_idx');
            $table->index(['event_type', 'occurred_at'], 'analytics_events_type_occurred_idx');
            $table->index(['user_id', 'occurred_at'], 'analytics_events_user_occurred_idx');
            $table->index(['visitor_hash', 'occurred_at'], 'analytics_events_visitor_occurred_idx');
            $table->index(['session_hash', 'occurred_at'], 'analytics_events_session_occurred_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
