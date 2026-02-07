# Angular frontend - Node 20, dev server with proxy to backend
FROM node:20-alpine

WORKDIR /app

# Copy package files
COPY package.json package-lock.json* ./

RUN npm ci && npm install -g @angular/cli

COPY . .

# Proxy /api to backend container when running in Docker
EXPOSE 4200
CMD ["ng", "serve", "--host", "0.0.0.0", "--proxy-config", "proxy.conf.docker.json"]
