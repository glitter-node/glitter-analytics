<?php
namespace Modules\Glitter\Analytics\Http\Resources;
use App\Http\Resources\BaseApiResource; use Carbon\CarbonImmutable; use Illuminate\Http\Request;
class AnalyticsVisitResource extends BaseApiResource {
 public function toArray(Request $r):array{$at=$this->getValue('occurred_at');$tz=(string)config('app.default_user_timezone','Asia/Seoul');return ['id'=>(int)$this->getValue('id'),'occurred_at'=>$at?CarbonImmutable::parse($at,'UTC')->setTimezone($tz)->format('Y-m-d H:i:s'):null,'path'=>$this->safePath((string)$this->getValue('path')),'source'=>$this->sourceValue(),'member'=>['is_member'=>$this->getValue('user_id')!==null,'id'=>$this->getValue('user_id')!==null?(int)$this->getValue('user_id'):null,'name'=>$this->getValue('user_name')?:null],'has_visitor'=>!empty($this->getValue('visitor_hash')),'has_session'=>!empty($this->getValue('session_hash')),'referrer_host'=>$this->safeHost($this->getValue('referrer_host'))];}
 private function sourceValue():?string{$s=$this->getValue('source');return $s instanceof \BackedEnum?(string)$s->value:($s===null?null:(string)$s);}
 private function safePath(string $path):string{$p=parse_url($path,PHP_URL_PATH);return is_string($p)&&str_starts_with($p,'/')&&!str_starts_with($p,'//')?mb_substr($p,0,512):'/';}
 private function safeHost(mixed $h):?string{if(!is_string($h)||$h==='')return null;$h=strtolower(trim($h));return preg_match('/^(?:[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?|\[[0-9a-f:]+\])(?::\d{1,5})?$/i',$h)?mb_substr($h,0,255):null;}
}
