<?php

namespace App\Enums;

enum AdvertisementStatusEnum: string
{
  const PENDING = 'pending';
  const APPROVED = 'approved';
  const REJECTED = 'rejected';
  const RUNNING = 'running';
  const PAUSED = 'paused';
  const EXPIRED = 'expired';  

  const AdvertisementStatus = [
        'pending' => 'Pending',
        'approved' => 'Approved', 
        'rejected' => 'Rejected',
        'running' => 'Running',
        'paused' => 'Paused',
        'expired' => 'Expired',
    ];
}
