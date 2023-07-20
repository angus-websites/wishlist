<?php

namespace App\Http\Controllers;

use App\Services\ProductScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductScraperController extends Controller
{
    protected $scraperService;

    public function __construct(ProductScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    public function scrapeProduct(Request $request)
    {
        // Ensure we have a url in the request
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $url = $request->input('url');

        // Use Laravel's HTTP client to get the HTML content
        // Retry up to 2 times, with a wait time of 3 seconds before giving up
        try {
            $response = retry(2, function () use ($url) {
                return Http::timeout(3)->withHeaders([
                    'User-Agent' => "MyWishlistApp lookup",
                ])->get($url);
            }, 1000);

            // If the request was successful, continue processing...
            if ($response->successful()) {
                $htmlContent = $response->body();

                // Scrape the data using ProductScraperService
                $product = $this->scraperService->scrapeProduct($htmlContent);

                return response()->json(["product" => $product]);
            }

        } catch (\Exception $exception) {

            Log::channel('scraper')->error('An error occurred during URL scraping', [
                'url' => $url,
                'error_message' => $exception->getMessage(),
            ]);
    
            // If the request was not successful after retrying, return a response indicating the error
            return response()->json(['error' => 'Unable to scrape product at this time. Please try again later.'], 500);
        }
    }


}

