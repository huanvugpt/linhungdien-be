<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Get 5 random different quotes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRandomQuotes()
    {
        try {
            // Approach đơn giản: Lấy tất cả quotes, tự filter unique content sau đó
            $allQuotes = Quote::all();
            
            // Group by content để lấy unique quotes
            $uniqueQuotes = $allQuotes->groupBy('content')
                ->map(function ($group) {
                    // Lấy quote đầu tiên trong mỗi group content
                    return $group->first();
                })
                ->values()
                ->shuffle()
                ->take(5);

            // Nếu không đủ 5 quotes unique trong database
            if ($uniqueQuotes->count() < 5) {
                $totalUniqueQuotes = $allQuotes->unique('content')->count();
                
                return response()->json([
                    'status' => true,
                    'message' => "Chỉ có {$totalUniqueQuotes} quotes unique trong hệ thống.",
                    'data' => [
                        'quotes' => $uniqueQuotes->map(function ($quote) {
                            return [
                                'id' => $quote->id,
                                'content' => $quote->content,
                                'created_at' => $quote->created_at
                            ];
                        }),
                        'total_available' => $totalUniqueQuotes,
                        'requested' => 5,
                        'returned' => $uniqueQuotes->count()
                    ]
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Lấy quotes ngẫu nhiên thành công.',
                'data' => [
                    'quotes' => $uniqueQuotes->map(function ($quote) {
                        return [
                            'id' => $quote->id,
                            'content' => $quote->content,
                            'created_at' => $quote->created_at
                        ];
                    }),
                    'count' => $uniqueQuotes->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to get random quotes', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi lấy quotes.',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all quotes with pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $quotes = Quote::select('id', 'content', 'created_at')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Lấy danh sách quotes thành công.',
                'data' => $quotes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to get quotes list', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách quotes.',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a single random quote
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingleRandomQuote()
    {
        try {
            $quote = Quote::select('id', 'content', 'created_at')
                ->inRandomOrder()
                ->first();

            if (!$quote) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có quotes nào trong hệ thống.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Lấy quote ngẫu nhiên thành công.',
                'data' => [
                    'quote' => $quote
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to get single random quote', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi lấy quote.',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
