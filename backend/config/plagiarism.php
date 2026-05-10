<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plagiarism Detection Threshold
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum allowed similarity percentage
    | before a thesis submission is rejected. Any thesis with a plagiarism
    | score equal to or above this threshold will be automatically rejected.
    |
    | Default: 40 (40% similarity)
    |
    */
    'threshold' => env('PLAGIARISM_THRESHOLD', 40),

    /*
    |--------------------------------------------------------------------------
    | Minimum Similarity to Report
    |--------------------------------------------------------------------------
    |
    | Only matches with similarity scores above this value will be included
    | in the plagiarism report.
    |
    | Default: 15 (15% similarity)
    |
    */
    'min_report_similarity' => env('PLAGIARISM_MIN_REPORT', 15),

    /*
    |--------------------------------------------------------------------------
    | Maximum Matches to Return
    |--------------------------------------------------------------------------
    |
    | The maximum number of similar theses to return in the plagiarism report.
    |
    | Default: 10
    |
    */
    'max_matches' => env('PLAGIARISM_MAX_MATCHES', 10),
];
