-- Migration: Add sentiment analysis fields to news_articles
-- Date: 2025-12-16

ALTER TABLE news_articles 
ADD COLUMN sentiment_score DECIMAL(3,2) NULL COMMENT 'Sentiment score between -1.0 and 1.0',
ADD COLUMN sentiment_label VARCHAR(20) NULL COMMENT 'Sentiment label: very_negative, negative, neutral, positive, very_positive',
ADD COLUMN importance_level VARCHAR(20) NULL COMMENT 'Importance level: low, medium, high, critical',
ADD COLUMN analyzed_at TIMESTAMP NULL COMMENT 'When sentiment analysis was performed',
ADD INDEX idx_sentiment_score (sentiment_score),
ADD INDEX idx_importance_level (importance_level),
ADD INDEX idx_analyzed_at (analyzed_at);

-- Ajoute les métadonnées pour les symboles affectés
ALTER TABLE news_articles
ADD COLUMN affected_symbols JSON NULL COMMENT 'List of cryptocurrency symbols mentioned in the article';

COMMENT ON TABLE news_articles IS 'News articles with sentiment analysis and importance scoring';
