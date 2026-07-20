# 🚀 Login via QR

### 😩 Ever tried typing your password on...

* 📺 a giant smartboard?
* 🎓 a classroom PC with a sticky keyboard?
* 🖥️ a public computer lab?
* 📡 a Raspberry Pi connected to a TV? (For whatever reason you wanna login to Moodle...)
* 😵 a keyboard where **Caps Lock** has a personality of its own?

If your answer is **"Unfortunately, yes."**, this plugin is for you.

Simply open the login page, scan the QR code with a device that's already signed in (usually your phone 📱), approve the login, and you're in.

No typing.
No forgotten passwords.
No "Is it my username or my email?"

Just **Scan → Confirm → Login.** 🎉

---

### 🔐 How it works

When the login page is opened, Moodle creates a temporary QR login request containing a random token and a secret. The QR code only contains the token.

After scanning the QR code with a device that's already signed in, the user confirms the login. Moodle then verifies that the request belongs to the same browser that generated the QR code before completing the login. Once used (or expired), the QR login request is removed and cannot be used again.

---

## Installing via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/public/auth/qrcode

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Setup

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Authentication > Manage authentication_.
2. Enable the _QR-Code_ plugin by clicking the icon in the _Enable_ column of the _Available authentication plugins_ table.
3. Configure the plugin settings as needed.

## License

2026 MoodleMootDACH

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
