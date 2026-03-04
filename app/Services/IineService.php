<?php
namespace App\Services;

// modelのインポート
use App\Models\Iine;
use Auth;
class IineService
{

  public function getIineCount($id)
  {
    $iine = Iine::where('target_id',$id)->count();
    return $iine;
  }
}