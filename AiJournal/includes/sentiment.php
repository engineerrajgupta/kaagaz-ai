<?php
/**
 * Analyze sentiment of text using the Python script
 * 
 * @param string $text Text to analyze
 * @return array Sentiment analysis result with 'score' and 'label'
 */
function analyzeSentiment($text) {
    // Default return if Python script fails
    $default = [
        'score' => 0,
        'label' => 'Neutral'
    ];
    
    // Clean and escape the text for shell usage
    $cleanText = escapeshellarg($text);
    
    // Path to Python script (using absolute path for reliability)
    $scriptPath = __DIR__ . '/../sentiment/analyze.py';
    
    // Check if script exists
    if (!file_exists($scriptPath)) {
        return $default;
    }
    
    // Try using the system's python or python3 command
    $pythonCmd = 'python';
    
    // Execute the sentiment analysis script
    $command = "$pythonCmd $scriptPath $cleanText 2>&1";
    $output = shell_exec($command);
    
    // If execution failed, try with python3
    if (empty($output) || strpos($output, 'Error') !== false) {
        $pythonCmd = 'python3';
        $command = "$pythonCmd $scriptPath $cleanText 2>&1";
        $output = shell_exec($command);
    }
    
    // Parse the JSON output
    if (!empty($output)) {
        $result = json_decode($output, true);
        if (is_array($result) && isset($result['score']) && isset($result['label'])) {
            return $result;
        }
    }
    
    // If all else fails, provide a simple sentiment analysis fallback
    $fallbackScore = simpleSentimentFallback($text);
    
    // Determine label based on score
    if ($fallbackScore > 0.1) {
        $label = "Positive";
    } elseif ($fallbackScore < -0.1) {
        $label = "Negative";
    } else {
        $label = "Neutral";
    }
    
    return [
        'score' => $fallbackScore,
        'label' => $label
    ];
}

/**
 * Very basic sentiment analysis fallback in PHP
 * Only use if Python script fails
 * 
 * @param string $text Text to analyze
 * @return float Simple sentiment score
 */
function simpleSentimentFallback($text) {
    // Lists of positive and negative words
    $positiveWords = [
        'good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic',
        'happy', 'joy', 'joyful', 'love', 'loving', 'awesome', 'nice',
        'beautiful', 'best', 'better', 'positive', 'success', 'successful',
        'win', 'winning', 'pleasure', 'pleasant', 'enjoy', 'enjoyable',
        'glad', 'delighted', 'grateful', 'thankful', 'excited', 'remarkable'
    ];
    
    $negativeWords = [
        'bad', 'terrible', 'awful', 'horrible', 'hate', 'sad', 'angry',
        'upset', 'unfortunate', 'disappointing', 'disappointed', 'negative',
        'fail', 'failure', 'poor', 'worst', 'worse', 'trouble', 'difficult',
        'unhappy', 'worried', 'annoyed', 'annoying', 'anxious', 'depressed',
        'miserable', 'regret', 'sorry', 'problem', 'dislike', 'pain', 'painful'
    ];
    
    // Convert to lowercase for comparison
    $text = strtolower($text);
    
    // Split into words
    $words = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    
    // Count occurrences
    $positiveCount = 0;
    $negativeCount = 0;
    $totalWords = count($words);
    
    foreach ($words as $word) {
        if (in_array($word, $positiveWords)) {
            $positiveCount++;
        } elseif (in_array($word, $negativeWords)) {
            $negativeCount++;
        }
    }
    
    // Calculate score between -1 and 1
    if ($totalWords > 0) {
        return ($positiveCount - $negativeCount) / $totalWords;
    } else {
        return 0;
    }
}
?> 