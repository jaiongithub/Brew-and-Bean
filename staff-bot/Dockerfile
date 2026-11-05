FROM mcr.microsoft.com/playwright:v1.42.1-jammy

WORKDIR /app

COPY package.json ./
RUN npm install --omit=dev

COPY bot.js ./

ENV NODE_ENV=production

CMD ["node", "bot.js"]

