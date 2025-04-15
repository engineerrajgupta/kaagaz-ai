#!/usr/bin/env python3
import sys
import json
from textblob import TextBlob

def analyze_sentiment(text):
    """
    Analyze the sentiment of a text using TextBlob
    Returns sentiment score (-1 to 1) and sentiment label
    """
    # Create TextBlob object
    analysis = TextBlob(text)
    
    # Get sentiment polarity (-1 to 1)
    score = analysis.sentiment.polarity
    
    # Determine sentiment label
    if score > 0.1:
        label = "Positive"
    elif score < -0.1:
        label = "Negative"
    else:
        label = "Neutral"
    
    return {
        "score": score,
        "label": label
    }

if __name__ == "__main__":
    # Read input text from command-line argument or stdin
    if len(sys.argv) > 1:
        text = sys.argv[1]
    else:
        text = sys.stdin.read()
    
    # Analyze sentiment
    result = analyze_sentiment(text)
    
    # Output as JSON
    print(json.dumps(result)) 