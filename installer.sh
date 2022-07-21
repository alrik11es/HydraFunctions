#!/usr/bin/env bash
cat << EOF
    __  ___     __        ________                 __  _
   /  |/  /__  / /_____ _/ / ____/_  ______  _____/ /_(_)___  ____  _____
  / /|_/ / _ \/ __/ __  / / /_  / / / / __ \/ ___/ __/ / __ \/ __ \/ ___/
 / /  / /  __/ /_/ /_/ / / __/ / /_/ / / / / /__/ /_/ / /_/ / / / (__  )
/_/  /_/\___/\__/\__,_/_/_/    \__,_/_/ /_/\___/\__/_/\____/_/ /_/____/
                                                INSTALLER

Welcome to the MF installer. This was created to help you to prepare a
PHP FullStack node server.


EOF

RED='\033[0;31m'
NC='\033[0m' # No Color

echo "1) PHP FullStack install (PHP 8.0 + NGINX)"
echo ""
echo "Please select a number and press enter to continue:";
read MF;

case $MF in
        "1")
          INSTALLABLE=true
            if command -v php &> /dev/null
            then
                echo -e "${RED}ERROR${NC}: PHP Already exists on this system."
                INSTALLABLE=false
            fi
            if command -v nginx &> /dev/null
            then
                echo -e "${RED}ERROR${NC}: NGINX Already exists on this system."
                INSTALLABLE=false
            fi
            if [[ $INSTALLABLE == true ]]; then
              echo "PHP FullStack install selected";
              #sudo su root ./bin/wsl-init.sh
            else
              echo "Your system is not FullStack eligible. Please install MF only and follow the steps in README";
            fi
          ;;
        *)
          echo "Not a valid argument"
          echo
          ;;
esac