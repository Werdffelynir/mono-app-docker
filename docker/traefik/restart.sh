#!/bin/bash

docker restart traefik_proxy
docker logs traefik_proxy
# docker-compose down -v && docker-compose up -d
# docker-compose down -v --rmi
