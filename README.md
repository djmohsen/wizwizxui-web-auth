# wizwizxui-web-auth

Minimal web authentication and VPN provisioning frontend that integrates with the wizwizxui-timebot project. It authenticates users against an IBSng JSON endpoint, asks for an international phone number (E.164) to use as VPN user id, and calls existing provisioning functions (addUser/addInboundAccount/getConnectionLink) from the wizwizxui-timebot config.php.

Drop these files into your server where the wizwizxui-timebot repository config.php is available. Edit auth.php to configure the IBSng endpoint if needed.

Files included:
- login.php
- auth.php
- panel.php
- create_vpn.php
- logout.php

Note: This project expects to be run alongside the wizwizxui-timebot project so that config.php and its provisioning functions are available. Adjust require paths if you place these files in a subdirectory.
