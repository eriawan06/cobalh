<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Validation\ValidationException;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('user')->OrderBy('id')->paginate(10)->toArray();
        return response()->json([
            'is_success' => true,
            'data' => $campaigns['data'],
            'total_count' => $campaigns['total'],
            'pagination' => [
                'next_page' => $campaigns['next_page_url'],
                'current_page' => $campaigns['current_page'],
            ],
        ], 200);
    }

    public function show($id)
    {
        $campaign = Campaign::with('user')->find($id);

        if (!$campaign) {
            throw new NotFoundHttpException('No data available');
        }

        return response()->json([
            'is_success' => true,
            'data' => $campaign,
        ], 200);
    }

    public function get_my_campaigns($userId)
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser->id != $userId) {
            throw new BadRequestException();
        }

        $campaigns = Campaign::where('user_id', $userId)->paginate(10)->toArray();

        if (!$campaigns) {
            throw new NotFoundHttpException('No data available');
        }

        return response()->json([
            'is_success' => true,
            'data' => $campaigns['data'],
            'total_count' => $campaigns['total'],
            'pagination' => [
                'next_page' => $campaigns['next_page_url'],
                'current_page' => $campaigns['current_page'],
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validationRules = [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'url' => 'required|string',
            'location' => 'required|string',
            'city' => 'required|string',
            'target_amount' => 'required|integer|min:1',
            'act_date' => 'required|date_format:Y-m-d H:i:s',
            'deadline' => 'required|date_format:Y-m-d',
            'banner_img' => 'required|string',
            'description' => 'required|string',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $authenticatedUser = Auth::user();
        if ($authenticatedUser->id != $input['user_id']) {
            throw new BadRequestException();
        }

        $campaign = Campaign::create($input);

        return response()->json([
            'is_success' => true,
            'data' => $campaign,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            throw new NotFoundHttpException('No data available');
        }

        $authenticatedUser = Auth::user();
        if ($authenticatedUser->role == 'user') {
            if ($authenticatedUser->id != $campaign->user_id) {
                throw new BadRequestException();
            }
        }

        $input = $request->all();
        $validationRules = [
            'title' => 'required|string',
            'url' => 'required|string',
            'location' => 'required|string',
            'city' => 'required|string',
            'target_amount' => 'required|integer|min:1',
            'act_date' => 'required|date_format:Y-m-d H:i:s',
            'deadline' => 'required|date_format:Y-m-d',
            'banner_img' => 'required|string',
            'description' => 'required|string',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $campaign->fill($input);
        $campaign->save();

        return response()->json([
            'is_success' => true,
            'data' => $campaign,
        ], 200);
    }

    public function destroy($id)
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            throw new NotFoundHttpException('No data available');
        }

        $authenticatedUser = Auth::user();
        if ($authenticatedUser->role == 'user') {
            if ($authenticatedUser->id != $campaign->user_id) {
                throw new BadRequestException();
            }
        }
        
        $campaign->delete();
        return response()->json(['is_success' => true], 200);
    }

    public static function update_amount($id, $amount)
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            throw new NotFoundHttpException('No data available');
        }

        $campaign->current_amount += $amount;
        if ($campaign->current_amount == $campaign->target_amount) {
            $campaign->is_completed = true;
        }
        $campaign->save();
    }
}
