# invest.ia Frontend (Web)

Application web frontend pour la plateforme invest.ia.

## Stack technique

- **React 18** avec TypeScript
- **Vite** (build tool)
- **React Router** (navigation)
- **Zustand** (state management)
- **React Query** (data fetching)
- **Recharts** (graphiques)
- **Axios** (HTTP client)

## Structure

```
src/
├─ components/           # Composants réutilisables
│  ├─ common/           # Boutons, inputs, cards...
│  ├─ market/           # Composants markets
│  ├─ portfolio/        # Composants portfolio
│  ├─ bots/             # Composants bots
│  ├─ news/             # Composants news
│  └─ risk/             # Composants risk center
├─ pages/               # Pages/routes
│  ├─ Overview.tsx
│  ├─ Markets.tsx
│  ├─ Portfolio.tsx
│  ├─ Bots.tsx
│  ├─ News.tsx
│  ├─ RiskCenter.tsx
│  └─ Settings.tsx
├─ hooks/               # Custom hooks
├─ services/            # API services
├─ stores/              # Zustand stores
├─ utils/               # Utilitaires
├─ types/               # Types TypeScript
├─ styles/              # Styles globaux
├─ App.tsx
└─ main.tsx
```

## Installation

```bash
npm install
```

## Développement

```bash
npm run dev
```

Ouvre [http://localhost:5173](http://localhost:5173)

## Build

```bash
npm run build
```

## Preview production

```bash
npm run preview
```

## Conventions

- Composants React : PascalCase
- Hooks : camelCase avec préfixe `use`
- Types : PascalCase avec suffixe `Type` si nécessaire
- Constants : UPPER_SNAKE_CASE
- Fichiers : PascalCase pour composants, camelCase pour utilitaires

## API

Communication avec le backend via `services/api.ts` :

```typescript
import { api } from '@/services/api';

const markets = await api.markets.getAll();
```
