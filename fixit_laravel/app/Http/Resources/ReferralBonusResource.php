<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class ReferralBonusResource  extends BaseResource
{
  protected $showSensitiveAttributes = true;

  public static $wrap = null;

  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    $referralBonus = [
      'id' => $this->id,
      'referrer_bonus_amount' => $this->bonus_amount,
      'referred_bonus_amount' => $this->referred_bonus_amount,
      'booking_amount' => $this->booking_amount,
      'referrer_percentage' => $this->referrer_percentage,
      'referred_percentage' => $this->referred_percentage,
      'referrer_type' => $this->referrer_type,
      'referred_type' => $this->referred_type,
      'status' => $this->status,
      'credited_at' => $this->credited_at,
      'referrer' => null,
      'referred' => null,
    ];

    if($this->referred) {
      $referralBonus['referred'] = [
        'id' => $this->referred?->id ?? null,
        'name' => $this->referred?->name,
        'email' => $this->referred?->email,
        'media' => $this->referred?->media?->map(function ($media) {
          return [
            'original_url' => $media?->original_url,
          ];
        }),
      ];
    }

    if($this->referrer) {
      $referralBonus['referrer'] = [
        'id' => $this->referrer?->id ?? null,
        'name' => $this->referrer?->name,
        'email' => $this->referrer?->email,
        'media' => $this->referrer?->media?->map(function ($media) {
          return [
            'original_url' => $media?->original_url,
          ];
        }),
      ];
    }

    return $referralBonus;
  }
}
