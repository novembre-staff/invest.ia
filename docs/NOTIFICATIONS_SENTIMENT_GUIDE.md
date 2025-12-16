# Système de Notifications & Analyse de Sentiment

Ce guide explique comment utiliser le système d'analyse de sentiment et de notifications multi-canal d'invest.ia.

---

## 📊 Analyse de Sentiment

### Vue d'ensemble

Le système analyse automatiquement le sentiment des actualités financières et crypto pour déterminer leur importance et leur impact potentiel sur le marché.

### Score de sentiment

- **Range** : -1.0 (très négatif) à +1.0 (très positif)
- **Labels** :
  - `very_negative` : score ≤ -0.6
  - `negative` : -0.6 < score ≤ -0.2
  - `neutral` : -0.2 < score ≤ 0.2
  - `positive` : 0.2 < score ≤ 0.6
  - `very_positive` : score > 0.6

### Calcul d'importance

4 critères évalués :
1. **Sentiment extrême** : +2 points si |score| > 0.6
2. **Source fiable** : +1 point si reliability ≥ 8/10
3. **Actifs suivis** : +2 points si mentionne des actifs en watchlist
4. **Impact marché** : +2 points si keywords détectés

**Niveaux** :
- `low` : 0-1 points
- `medium` : 2-3 points
- `high` : 4-5 points (⚠️ déclenche alertes)
- `critical` : 6+ points (🚨 alertes prioritaires)

---

## 🔔 Canaux de Notification

### 1. Email
- Templates HTML avec styling
- Liens cliquables vers articles
- Métadonnées formatées
- Configuration : `MAIL_FROM`

### 2. Push Notifications
- Firebase Cloud Messaging
- Notifications mobiles natives
- Data payload avec métadonnées
- Configuration : `FCM_SERVER_KEY`

### 3. SMS
- Via Twilio
- Limité à 320 caractères (2 segments)
- Format compact optimisé
- Configuration : `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER`

### 4. Discord
- Webhooks avec embeds colorés
- Couleurs selon sentiment (vert/rouge/bleu)
- Liens et champs structurés
- Configuration : `DISCORD_WEBHOOK_URL`

### 5. Telegram
- Bot API avec Markdown
- Emojis et formatage
- Liens inline
- Configuration : `TELEGRAM_BOT_TOKEN`

---

## 🚀 Utilisation

### 1. Analyser une actualité

```bash
curl -X POST http://localhost:8000/api/news/123/analyze \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Réponse** :
```json
{
  "success": true,
  "message": "Sentiment analysis started",
  "news_id": "123"
}
```

### 2. Analyse en batch

```bash
curl -X POST http://localhost:8000/api/news/analyze-batch \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"news_ids": ["123", "124", "125"]}'
```

**Réponse** :
```json
{
  "success": true,
  "message": "Analysis started for 3 articles",
  "count": 3
}
```

### 3. Récupérer actualités importantes

```bash
curl http://localhost:8000/api/news/important \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Réponse** :
```json
{
  "success": true,
  "data": [
    {
      "id": "123",
      "title": "Bitcoin breaks $100k",
      "summary": "Historic milestone...",
      "source": "CoinDesk",
      "url": "https://...",
      "category": "crypto",
      "symbols": ["BTC", "ETH"],
      "importance": 8.5,
      "sentiment": {
        "label": "very_positive",
        "score": 0.85,
        "confidence": 0.92
      },
      "published_at": "2025-12-16T10:30:00Z",
      "is_high_impact": true
    }
  ],
  "count": 1
}
```

---

## ⚙️ Configuration

### Variables d'environnement (.env)

```bash
###> Notifications ###
MAIL_FROM=noreply@invest.ia

# Firebase Push Notifications
FCM_SERVER_KEY=your_firebase_server_key

# Twilio SMS
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890

# Discord Webhook
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/123/abc

# Telegram Bot
TELEGRAM_BOT_TOKEN=123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
###< Notifications ###

###> Sentiment Analysis ###
# Simple analyzer (par défaut, pas de config)
# OU OpenAI analyzer :
OPENAI_API_KEY=sk-xxxxxxxxxxxxxxxx
###< Sentiment Analysis ###
```

### Choisir l'analyseur de sentiment

**Option 1 : Simple Analyzer** (par défaut)
- Analyse basée sur keywords
- Pas de dépendance externe
- Gratuit
- Précision : ~70%

```yaml
# config/services.yaml
App\News\Domain\Service\SentimentAnalyzerInterface:
    class: App\News\Infrastructure\Service\SimpleSentimentAnalyzer
```

**Option 2 : OpenAI Analyzer** (recommandé production)
- Analyse NLP avec GPT
- Nécessite clé API
- Coût par requête
- Précision : ~95%

```yaml
# config/services.yaml
App\News\Domain\Service\SentimentAnalyzerInterface:
    class: App\News\Infrastructure\Service\OpenAISentimentAnalyzer
    arguments:
        $apiKey: '%env(OPENAI_API_KEY)%'
        $model: 'gpt-3.5-turbo'
```

---

## 🔄 Workflow automatique

1. **Nouvelle actualité publiée**
   - Sauvegardée en base avec `ImportanceScore` initial

2. **Analyse automatique** (async)
   - Command `AnalyzeNewsSentiment` dispatché
   - Analyseur calcule sentiment
   - Détection symboles mentionnés
   - Calcul importance finale

3. **Event NewsAnalyzed**
   - Émis pour chaque analyse complétée
   - Utilisable pour analytics/logging

4. **Event ImportantNewsDetected** (si high/critical)
   - Émis uniquement pour actualités importantes
   - Déclenche le listener d'alertes

5. **Alertes automatiques**
   - Listener trouve utilisateurs concernés (watchlists)
   - Command `SendNewsAlert` dispatché par utilisateur
   - Notifications envoyées sur canaux préférés

---

## 👥 Configuration utilisateur

### Activer les alertes

```php
// Via préférences utilisateur
$user->updatePreferences([
    'news_alerts_enabled' => true,
    'push_notifications_enabled' => true,
    'sms_alerts_enabled' => false, // Optionnel
]);
```

### Lier comptes tiers

```php
// Discord
$user->linkDiscordAccount('discord_user_id');

// Telegram
$user->linkTelegramAccount('telegram_chat_id');
```

---

## 🧪 Tests

```bash
# Lancer tous les tests
composer test

# Tests sentiment analysis uniquement
vendor/bin/phpunit tests/News/Domain/ValueObject/SentimentScoreTest.php
vendor/bin/phpunit tests/News/Domain/ValueObject/NewsImportanceTest.php
vendor/bin/phpunit tests/News/Infrastructure/Service/SimpleSentimentAnalyzerTest.php
```

---

## 📊 Monitoring

### Logs

Les notifications sont loggées automatiquement :
```
[info] Notification sent via email to user@example.com
[info] Notification sent via discord to webhook_123
[error] Failed to send notification via sms: Invalid phone number
```

### Métriques à surveiller

- Taux d'analyse (articles/heure)
- Distribution des sentiments
- Distribution des importances
- Taux d'envoi par canal
- Taux d'erreur par canal
- Latence moyenne d'analyse

---

## 🐛 Troubleshooting

### L'analyse de sentiment ne fonctionne pas

**Vérifier** :
1. Service configuré dans `services.yaml`
2. Variables d'environnement (si OpenAI)
3. Logs Symfony : `var/log/dev.log`
4. Command handler enregistré dans messenger

### Les notifications ne sont pas envoyées

**Vérifier** :
1. Canaux configurés (env vars)
2. Préférences utilisateur (alertes activées)
3. Utilisateur a des actifs en watchlist
4. Article a importance ≥ high
5. Workers messenger en cours : `php bin/console messenger:consume async`

### Erreur "Channel not available"

Le canal n'est pas configuré. Ajouter les variables d'environnement nécessaires et redémarrer l'app.

---

## 🔐 Sécurité

### Clés API

- ⚠️ Ne jamais commit les clés dans git
- ✅ Utiliser `.env.local` pour dev
- ✅ Utiliser variables d'environnement serveur pour prod
- ✅ Rotation régulière des clés

### Rate Limiting

Implémenter rate limiting sur endpoints publics :
```yaml
# config/packages/rate_limiter.yaml
framework:
    rate_limiter:
        analyze_news:
            policy: 'token_bucket'
            limit: 10
            rate: { interval: '1 minute' }
```

---

## 📚 Ressources

### Documentation API externes

- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
- [Twilio SMS API](https://www.twilio.com/docs/sms)
- [Discord Webhooks](https://discord.com/developers/docs/resources/webhook)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [OpenAI API](https://platform.openai.com/docs)

### Code examples

Voir `tests/` pour exemples d'utilisation de chaque composant.

---

**Besoin d'aide ?** Consulter la doc complète dans `docs/conception/SPRINT_10_RECAP.md`
