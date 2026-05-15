#!/bin/bash
# Deployment script for alwaysdata.net
# Run this script via SSH on your alwaysdata.net account

set -e  # Exit on error

echo "=========================================="
echo "Sports Bottles Deployment to alwaysdata.net"
echo "=========================================="

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Navigate to web root
echo -e "${BLUE}[1/7] Navigating to web root...${NC}"
cd /home/$USER/www
echo -e "${GREEN}✓ Current directory: $(pwd)${NC}"

# 2. Clone or pull repository
if [ -d "sport_bottles" ]; then
    echo -e "${BLUE}[2/7] Repository exists, pulling latest changes...${NC}"
    cd sport_bottles
    git pull origin master
else
    echo -e "${BLUE}[2/7] Cloning repository...${NC}"
    git clone https://github.com/YOUR_USERNAME/sport_bottles.git sport_bottles
    cd sport_bottles
fi
echo -e "${GREEN}✓ Repository ready${NC}"

# 3. Check .env.local exists
echo -e "${BLUE}[3/7] Checking environment configuration...${NC}"
if [ ! -f ".env.local" ]; then
    echo -e "${YELLOW}⚠ .env.local not found. Creating from template...${NC}"
    cp .env.prod .env.local
    echo -e "${YELLOW}⚠ MANUAL STEP: Edit .env.local with your database credentials:${NC}"
    echo "   nano .env.local"
    echo ""
    echo "   Replace:"
    echo "   - DATABASE_URL with your MySQL credentials from alwaysdata.net control panel"
    echo "   - APP_SECRET with a random secret key"
    echo "   - DEFAULT_URI with your domain"
    echo "   - STRIPE keys (if needed)"
    echo ""
    echo -e "${YELLOW}After editing, run this script again.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ .env.local exists${NC}"

# 4. Install Composer dependencies
echo -e "${BLUE}[4/7] Installing PHP dependencies...${NC}"
composer install --no-dev --optimize-autoloader
echo -e "${GREEN}✓ Composer dependencies installed${NC}"

# 5. Create necessary directories
echo -e "${BLUE}[5/7] Setting up directories...${NC}"
mkdir -p var/cache var/log var/tailwind
chmod 755 var/cache var/log var/tailwind
echo -e "${GREEN}✓ Directories ready${NC}"

# 6. Run database migrations
echo -e "${BLUE}[6/7] Running database migrations...${NC}"
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
echo -e "${GREEN}✓ Database migrations complete${NC}"

# 7. Clear cache and compile assets
echo -e "${BLUE}[7/7] Clearing cache and compiling assets...${NC}"
php bin/console cache:clear --env=prod
php bin/console asset-map:compile --env=prod
echo -e "${GREEN}✓ Cache cleared and assets compiled${NC}"

echo ""
echo -e "${GREEN}=========================================="
echo "✓ Deployment successful!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. In alwaysdata.net control panel (Web → Domains):"
echo "   - Set Web root to: /home/$USER/www/sport_bottles/public"
echo "2. Enable mod_rewrite if not already enabled"
echo "3. Visit: https://sports-bottles.alwaysdata.net/"
echo ""
echo "To update in the future, just run:"
echo "  cd /home/$USER/www/sport_bottles && git pull && composer install --no-dev && php bin/console cache:clear --env=prod"
echo ""
