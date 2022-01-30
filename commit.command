#!/bin/bash

echo 
echo 进入目录:`dirname $0`
cd `dirname $0`

git pull
git add .
git commit -m "cmd auto submit"
git push


