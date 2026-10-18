<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('analytics_events',function(Blueprint $t):void{$t->index(['source','occurred_at'],'analytics_events_source_occurred_idx');$t->index(['occurred_at','path'],'analytics_events_occurred_path_idx');});}
 public function down():void{Schema::table('analytics_events',function(Blueprint $t):void{$t->dropIndex('analytics_events_source_occurred_idx');$t->dropIndex('analytics_events_occurred_path_idx');});}
};
