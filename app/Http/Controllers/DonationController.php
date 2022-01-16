<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Validation\ValidationException;

class DonationController extends Controller
{
    public function index()
    {
        $donations = Donation::with('user', 'campaign.user')->OrderBy('id')->paginate(10)->toArray();
        return response()->json([
            'is_success' => true,
            'data' => $donations['data'],
            'total_count' => $donations['total'],
            'pagination' => [
                'next_page' => $donations['next_page_url'],
                'current_page' => $donations['current_page'],
            ],
        ], 200);
    }

    public function show($id)
    {
        $donation = Donation::with('user', 'campaign.user')->find($id);

        if (!$donation) {
            throw new NotFoundHttpException('No data available');
        }

        return response()->json([
            'is_success' => true,
            'data' => $donation,
        ], 200);
    }

    public function get_my_donations($userId)
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser->id != $userId) {
            throw new BadRequestException();
        }

        $donations = Donation::where('user_id', $userId)->with('user', 'campaign.user')->paginate(10)->toArray();

        if (!$donations) {
            throw new NotFoundHttpException('No data available');
        }

        return response()->json([
            'is_success' => true,
            'data' => $donations['data'],
            'total_count' => $donations['total'],
            'pagination' => [
                'next_page' => $donations['next_page_url'],
                'current_page' => $donations['current_page'],
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $isPublic = $request->is('api/public/*');
        $input = $request->all();
        $validationRules = [
            'user_id' => ($isPublic ? '' : 'required|') . 'exists:users,id|nullable',
            'campaign_id' => 'required|exists:campaigns,id',
            'name' => $isPublic ? 'required|string' : 'string|nullable',
            'email' => $isPublic ? 'required|email' : 'email|nullable',
            'phone' => $isPublic ? 'required|numeric' : 'numeric|nullable',
            'comment' => 'string',
            'amount' => 'required|integer|min:10000',
            'is_anonim' => 'required|boolean',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
        
        if (!$isPublic) {
            $authenticatedUser = Auth::user();
            if ($authenticatedUser->id != $input['user_id']) {
                throw new BadRequestException();
            }
        }

        $campaign = Campaign::find($input['campaign_id']);
        if ($campaign->is_completed) {
            throw new BadRequestException('Campaign has been closed');
        }

        $donation = Donation::create($input);

        return response()->json([
            'is_success' => true,
            'data' => $donation,
        ], 201);
    }

    public function confirm(Request $request, $donationId)
    {
        $isPublic = $request->is('api/public/*');
        $input = $request->all();
        $validationRules = [
            'payment_method' => 'required|in:BCA,BRI,BNI,Mandiri',
            'evidence' => 'required|string',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $donation = Donation::with('user', 'campaign.user')->find($donationId);
        // $donation = Donation::find($donationId);
        
        if (!$isPublic) {
            $authenticatedUser = Auth::user();
            if ($authenticatedUser->id != $donation->user_id) {
                throw new BadRequestException();
            }
        }

        if ($donation->is_paid) {
            throw new BadRequestException('Has been paid');
        }

        $donation->payment_method = $input['payment_method'];
        $donation->evidence = $input['evidence'];
        $donation->is_paid = true;
        $donation->paid_at = date('Y-m-d H:i:s');
        $donation->save();

        CampaignController::update_amount($donation->campaign_id, $donation->amount);

        return response()->json([
            'is_success' => true,
            'data' => Donation::with('user', 'campaign.user')->find($donationId),
        ], 201);
    }
}
