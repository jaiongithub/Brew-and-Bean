#!/bin/bash
set -e

REGISTRY_USER=${DOCKER_USER:-"jaiongithub"}
IMAGE_NAME="brewandbean"
TAG="latest"

echo "==================================================="
echo "  BREW & BEAN - Image Build & Registry Push"
echo "==================================================="
echo ""
echo "Target Image: ${REGISTRY_USER}/${IMAGE_NAME}:${TAG}"
echo ""

echo "1. Running code obfuscation pipeline..."
python3 "to students/obfuscate.py"

echo ""
echo "2. Building Docker image..."
docker build -t ${REGISTRY_USER}/${IMAGE_NAME}:${TAG} -t ${IMAGE_NAME}:latest -f "to students/Dockerfile" .

echo ""
echo "3. Pushing image to Docker Registry (${REGISTRY_USER}/${IMAGE_NAME}:${TAG})..."
docker push ${REGISTRY_USER}/${IMAGE_NAME}:${TAG}

echo ""
echo "[+] Successfully built and published ${REGISTRY_USER}/${IMAGE_NAME}:${TAG}!"
