CREATE TABLE news_articles (
    id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    summary TEXT NOT NULL,
    content TEXT,
    source_url VARCHAR(1000) NOT NULL UNIQUE,
    source_name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    related_symbols JSONB NOT NULL DEFAULT '[]',
    importance_score INTEGER NOT NULL,
    published_at TIMESTAMP NOT NULL,
    image_url VARCHAR(1000),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_news_published_at ON news_articles(published_at DESC);
CREATE INDEX idx_news_category ON news_articles(category);
CREATE INDEX idx_news_importance_score ON news_articles(importance_score DESC);
CREATE INDEX idx_news_related_symbols ON news_articles USING GIN(related_symbols);

-- Comment for documentation
COMMENT ON TABLE news_articles IS 'News articles from external sources with importance scoring';
COMMENT ON COLUMN news_articles.importance_score IS 'Score from 0-100 indicating news importance (75+ triggers alerts)';
COMMENT ON COLUMN news_articles.related_symbols IS 'Array of crypto symbols mentioned in the news (e.g., ["BTC", "ETH"])';
