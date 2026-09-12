<?php

namespace App\Repositories\Frontend;

use Exception;
use App\Models\State;
use App\Models\Address;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Prettus\Repository\Eloquent\BaseRepository;

class AddressRepository extends BaseRepository
{
    public function model()
    {
        return Address::class;
    }
    public function store($request)
    {
        DB::beginTransaction();
        try {

            if (auth()->check()) {
                $user = auth()->user();
                if ($request->is_primary == true) {
                    $this->model->query()->where('user_id', $user->id)->update([
                        'is_primary' => false,
                    ]);
                }

                $this->model->create($this->addressPayload($request));

            } else {
                $addresses = session('addresses', []);
                $address =  $request->all();
                $address['type'] = $request?->address_type;
                $address['country'] = $this->getCountryById($request->country_id);
                $address['state'] = $this->getStateById($request->state_id);
                $address['address'] = $this->buildFullAddress($request->street_address, $request->city, $request->state_id);
                $addresses[] = $address;
                session(['addresses' => $addresses]);
            }

            DB::commit();
            return redirect()->back()->with('message', __('frontend::static.address.saved_successfully'));

        } catch (Exception $e) {
            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCountryById($country_id)
    {
        return Country::where('id', $country_id)?->first()?->toArray();
    }

    public function getStateById($state_id)
    {
        return State::where('id', $state_id)?->first()?->toArray();
    }

    /**
     * There is no separate free-text "address" field in the form anymore, so
     * it is derived from street+house and city. State/postal code/country
     * are deliberately left out — every place that displays `address`
     * (account address list, booking address picker) already appends them
     * separately, so including them here would duplicate them on screen.
     */
    public function buildFullAddress($streetAddress, $city, $stateId = null)
    {
        return implode(', ', array_filter([$streetAddress, $city]));
    }

    public function addressPayload($request)
    {
        return [
            'user_id' => auth()?->user()?->id,
            'type' => $request->address_type,
            'postal_code' => $request->postal_code,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city' => $request->city,
            'code' => $request->code,
            'address' => $this->buildFullAddress($request->street_address, $request->city, $request->state_id),
            'street_address' => $request->street_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'alternative_name' => $request->alternative_name,
            'alternative_phone' => $request->alternative_phone,
            'label' => $request->label,
            'is_primary' => $request->is_primary ?? 0,
        ];
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $address = $this->model->findOrFail($id);
            if (isset($request['is_primary']) == true) {
                if (($request['is_primary']) == true) {
                    $this->model->query()->where('user_id', auth()?->user()?->id)->update(['is_primary' => false]);
                }
            }
            $address->update([
                'user_id' => auth()?->user()?->id,
                'type'=> $request['address_type'],
                'alternative_name' => $request['alternative_name'],
                'code' => $request['code'],
                'alternative_phone' => $request['alternative_phone'],
                'postal_code' => $request['postal_code'],
                'country_id' => $request['country_id'],
                'state_id' => $request['state_id'],
                'city' => $request['city'],
                'address' => $this->buildFullAddress($request['street_address'], $request['city'], $request['state_id']),
                'street_address' => $request['street_address'],
                'latitude' => $request['latitude'] ?? $address?->latitude,
                'longitude' => $request['longitude'] ?? $address?->longitude,
                'label' => $request['label'] ?? null,
                'is_primary' => $request['is_primary'] ?? $address?->is_primary,
            ]);

            DB::commit();
            return redirect()->back()->with('message', __('frontend::static.address.updated_successfully'));
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function isPrimary($id)
    {
        DB::beginTransaction();
        try {
            $primaryAddress = $this->model->query()->where('user_id', auth()->user()->id);
            $primaryAddress->update(['is_primary' => false]);
            $addressExist = $this->model->query()->where('user_id', auth()->user()->id)->where('id', $id);
            $makePrimaryAddress = $addressExist->update(['is_primary' => true]);
            if (! $makePrimaryAddress) {
                throw new ExceptionHandler(__('errors.invalid_address_id'), 409);
            }
            DB::commit();

            return response()->json([
                'message' => __('static.address.primary_address_updated_successfully'),
                'status' => true,
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $address = $this->model->findOrFail($id);
            if (!$address) {
                throw new ExceptionHandler(__('static.address.id_not_valid'), Response::HTTP_BAD_REQUEST);
            }
            $address->destroy($id);

            return redirect()->back()->with('message', 'Address Deleted Successfully');
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}