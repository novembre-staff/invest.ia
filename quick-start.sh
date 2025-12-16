#!/bin/bash

# 🚀 Quick Start Script - invest.ia API
# Usage: ./quick-start.sh

set -e

echo "🚀 Starting invest.ia API setup..."
echo ""

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Install dependencies
echo -e "${BLUE}📦 Installing Composer dependencies...${NC}"
cd apps/api
composer install --no-interaction
echo -e "${GREEN}✅ Dependencies installed${NC}"
echo ""

# 2. Copy .env if not exists
if [ ! -f .env.local ]; then
    echo -e "${BLUE}📝 Creating .env.local from .env.example...${NC}"
    cp ../../.env.example .env.local
    
    # Generate APP_SECRET
    APP_SECRET=$(openssl rand -hex 32)
    sed -i "s/APP_SECRET=/APP_SECRET=$APP_SECRET/" .env.local
    
    echo -e "${YELLOW}⚠️  Please configure DATABASE_URL and other settings in apps/api/.env.local${NC}"
    echo ""
fi

# 3. Generate JWT keys
if [ ! -f config/jwt/private.pem ]; then
    echo -e "${BLUE}🔑 Generating JWT keypair...${NC}"
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
    echo -e "${GREEN}✅ JWT keys generated${NC}"
else
    echo -e "${GREEN}✅ JWT keys already exist${NC}"
fi
echo ""

# 4. Create database
echo -e "${BLUE}🗄️  Creating database...${NC}"
php bin/console doctrine:database:create --if-not-exists
echo -e "${GREEN}✅ Database created${NC}"
echo ""

# 5. Run migrations
echo -e "${BLUE}📊 Running migrations...${NC}"
php bin/console doctrine:migrations:migrate --no-interaction
echo -e "${GREEN}✅ Migrations executed${NC}"
echo ""

# 6. Clear cache
echo -e "${BLUE}🧹 Clearing cache...${NC}"
php bin/console cache:clear
echo -e "${GREEN}✅ Cache cleared${NC}"
echo ""

# 7. Run tests
echo -e "${BLUE}🧪 Running tests...${NC}"
php bin/phpunit
echo -e "${GREEN}✅ Tests passed${NC}"
echo ""

# Success
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Setup complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo ""
echo -e "1. Start the API server:"
echo -e "   ${YELLOW}symfony server:start${NC}"
echo -e "   OR"
echo -e "   ${YELLOW}php -S localhost:8000 -t public/${NC}"
echo ""
echo -e "2. Start the Messenger worker (in another terminal):"
echo -e "   ${YELLOW}php bin/console messenger:consume async -vv${NC}"
echo ""
echo -e "3. Test the API:"
echo -e "   ${YELLOW}curl -X POST http://localhost:8000/api/auth/register \\${NC}"
echo -e "   ${YELLOW}  -H 'Content-Type: application/json' \\${NC}"
echo -e "   ${YELLOW}  -d '{\"email\":\"test@example.com\",\"password\":\"SecurePass123\",\"firstName\":\"John\",\"lastName\":\"Doe\"}'${NC}"
echo ""
echo -e "4. Read the documentation:"
echo -e "   ${YELLOW}apps/api/README.md${NC}"
echo ""
