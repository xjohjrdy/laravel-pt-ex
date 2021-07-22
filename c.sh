#!/bin/bash




 
file="./.env_ol"
if [ -f "$file" ]
then
 echo -e "\033[32m 当前状态为DEBUG模式，即将修改成【线上】模式。 \033[0m"
 mv ./.env ./.env_de
 mv ./.env_ol ./.env
 exit
fi 

file="./.env_de"
if [ -f "$file" ]
then
 echo -e "\033[36m 当前状态为线上模式，即将修改成【DEBUG】模式。 \033[0m" 
 mv ./.env ./.env_ol
 mv ./.env_de ./.env
 exit
fi 
