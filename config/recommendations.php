<?php

return [
    'ratings' => [
        // m in the Bayesian weighted-rating formula.
        'minimum_ratings' => env('RATINGS_MINIMUM_RATINGS', 5),
        // C is cached because calculating platform-wide means scans rated models.
        'platform_mean_ttl' => env('RATINGS_PLATFORM_MEAN_TTL', 3600),
    ],

    'hybrid' => [
        // These must sum to 1.0.
        'similarity' => env('RECOMMENDATION_SIMILARITY_WEIGHT', 0.5),
        'weighted_rating' => env('RECOMMENDATION_RATING_WEIGHT', 0.3),
        'popularity' => env('RECOMMENDATION_POPULARITY_WEIGHT', 0.2),
    ],
];
