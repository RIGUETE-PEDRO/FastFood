<?php

namespace App\Services;

use App\Repository\GarcomRepository;

class GarcomService
{
    public function __construct(private GarcomRepository $repository)
    {
    }

   
}
