#!/bin/bash

echo 
echo 进入目录:`dirname $0`
cd `dirname $0`

git pull
git add --all
git commit -m "cmd auto submit"
git push


# 自动更新Tag ----------------------------------------
# 详见：https://github.com/wsdo/git-auto-tag/blob/master/tag.sh
# 0.1> 获取最新的版本号
VERSION=`git describe --abbrev=0 --tags`
# 0.11> 去看tag中的"/refs/tags"前缀
VERSION=(${VERSION##*refs/tags/}) # 从左向右截取最后一个字符串“refs/tags”后的字符串
echo "$VERSION";
# 0.2> 以点'.'分割字符串为数组
VERSION_BITS=(${VERSION//./ })
echo "$VERSION_BITS"
# 0.3> 获取数字部分，最近一位加1
VNUM1=${VERSION_BITS[0]}
VNUM2=${VERSION_BITS[1]}
VNUM3=${VERSION_BITS[2]}
VNUM3=$((VNUM3+1))
# 0.4> 拼接生成新的版本号
NEW_TAG="$VNUM1.$VNUM2.$VNUM3"
echo "更新 $VERSION 至 $NEW_TAG"

# 构建新tag,并推送
git tag $NEW_TAG -m "自动更新Tag，以便Composer更新版本号"
git push --tags