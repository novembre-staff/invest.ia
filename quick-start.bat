@echo off
REM 🚀 Quick Start Script - invest.ia API (Windows)
REM Usage: quick-start.bat

echo 🚀 Starting invest.ia API setup...
echo.

REM 1. Install dependencies
echo 📦 Installing Composer dependencies...
cd apps\api
call composer install --no-interaction
if errorlevel 1 (
    echo ❌ Composer install failed
    exit /b 1
)
echo ✅ Dependencies installed
echo.

REM 2. Copy .env if not exists
if not exist .env.local (
    echo 📝 Creating .env.local from .env.example...
    copy ..\..\\.env.example .env.local
    echo ⚠️  Please configure DATABASE_URL and other settings in apps\api\.env.local
    echo.
)

REM 3. Generate JWT keys
if not exist config\jwt\private.pem (
    echo 🔑 Generating JWT keypair...
    php bin\console lexik:jwt:generate-keypair --skip-if-exists
    echo ✅ JWT keys generated
) else (
    echo ✅ JWT keys already exist
)
echo.

REM 4. Create database
echo 🗄️  Creating database...
php bin\console doctrine:database:create --if-not-exists
echo ✅ Database created
echo.

REM 5. Run migrations
echo 📊 Running migrations...
php bin\console doctrine:migrations:migrate --no-interaction
echo ✅ Migrations executed
echo.

REM 6. Clear cache
echo 🧹 Clearing cache...
php bin\console cache:clear
echo ✅ Cache cleared
echo.

REM 7. Run tests
echo 🧪 Running tests...
php bin\phpunit
if errorlevel 1 (
    echo ⚠️  Some tests failed
) else (
    echo ✅ Tests passed
)
echo.

REM Success
echo ========================================
echo ✅ Setup complete!
echo ========================================
echo.
echo Next steps:
echo.
echo 1. Start PostgreSQL (if not running):
echo    net start postgresql-x64-15
echo.
echo 2. Start Redis (if not running):
echo    redis-server
echo.
echo 3. Start the API server:
echo    symfony server:start
echo    OR
echo    php -S localhost:8000 -t public/
echo.
echo 4. Start the Messenger worker (in another terminal):
echo    php bin/console messenger:consume async -vv
echo.
echo 5. Test the API:
echo    curl -X POST http://localhost:8000/api/auth/register ^
echo      -H "Content-Type: application/json" ^
echo      -d "{\"email\":\"test@example.com\",\"password\":\"SecurePass123\",\"firstName\":\"John\",\"lastName\":\"Doe\"}"
echo.
echo 6. Read the documentation:
echo    apps\api\README.md
echo.

cd ..\..
