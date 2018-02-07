<?php

namespace Devise\Resources\Api;

use Illuminate\Http\Resources\Json\Resource;

class FieldResource extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request
   * @return array
   */
  public function toArray($request)
  {
    $data = $this->value;

    $data->id = $this->id;

    return $data;
  }
}