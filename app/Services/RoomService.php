<?php
//namespace App\Services\IineService;
namespace App\Services;

// modelのインポート
use App\Models\Room;
use Auth;
class RoomService
{

  public function get($id)
  {
    $name = Room::where('id',$id)->first();
    return $name;
  }
}