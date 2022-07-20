#!/usr/bin/env bash
cat << EOF
    __  ___     __        ________                 __  _
   /  |/  /__  / /_____ _/ / ____/_  ______  _____/ /_(_)___  ____  _____
  / /|_/ / _ \/ __/ __  / / /_  / / / / __ \/ ___/ __/ / __ \/ __ \/ ___/
 / /  / /  __/ /_/ /_/ / / __/ / /_/ / / / / /__/ /_/ / /_/ / / / (__  )
/_/  /_/\___/\__/\__,_/_/_/    \__,_/_/ /_/\___/\__/_/\____/_/ /_/____/
                                                INSTALLER

Welcome to the MF installer. This was created to help you to prepare a
PHP FullStack node server. Or just to install MF.


EOF

echo "1) PHP FullStack install (PHP 8.0 + NGINX + MF)"
echo "2) MF install only"
echo ""
echo "Please select a number and press enter to continue:";
read mf;
if [[ "$mf" == "1" ]]; then
  echo "PHP FullStack install selected";
  sudo su root ./bin/wsl-init.sh
else
  echo "MF Install only selected";
#  wget latest
#  read -p "Make it global?"
#  sudo mv mf.phar /usr/local/bin/phf
fi