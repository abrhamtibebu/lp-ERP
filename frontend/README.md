# Parker Clay ERP Frontend

Vue 3 SPA frontend for the Parker Clay ERP System.

## Setup

1. Install dependencies:
```bash
npm install
```

2. Start development server:
```bash
npm run dev
```

The frontend will run on `http://localhost:3000` and proxy API requests to `http://localhost:8000/api`.

## Build

```bash
npm run build
```

Built files will be in the `dist` folder.

## Environment Variables

You can create a `.env` file to configure the API URL:

```
VITE_API_URL=http://localhost:8000/api
```

