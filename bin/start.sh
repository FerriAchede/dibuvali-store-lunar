#!/bin/bash
docker compose -f docker-compose.prod.yml --env-file .env.production up -d