# Chatbot Moodle Plugin

This plugin adds a simple chat icon to Moodle pages so selected users can interact with a backend chatbot service. It is designed as a `local` plugin and can be installed from the Moodle admin interface by uploading the plugin ZIP file.

## Installation

1. Zip the `local` directory into `chatbot.zip` so that the resulting archive contains the `chatbot` folder inside `local/`.
2. Log in as an administrator and navigate to **Site administration > Plugins > Install plugins**.
3. Upload `chatbot.zip` and follow the on‑screen instructions to complete the installation.
4. After installation go to **Site administration > Plugins > Local plugins > Chatbot** to manage which students can access the chatbot.

## Configuration

Use the management page to search for students and enable or disable the chatbot for each of them. Only students marked as enabled will see the chat icon.

## Usage

When enabled for a user, a smiley chat icon appears at the bottom right of every page. Clicking the icon opens a small chat window. Any message typed by the user triggers a two‑second “Agent is thinking…” indicator before replying with the fixed text “Hi there”.

The plugin passes the user ID and the current course name (if available) to the JavaScript widget. The included widget simply logs this information to the browser console. Replace the logic inside `amd/src/chatbot.js` with your integration code to connect to your chosen backend service.

## Packaging the Plugin

1. Ensure the folder structure is:

```
local/
  chatbot/
    version.php
    lib.php
    settings.php
    lang/en/local_chatbot.php
    amd/
      src/chatbot.js
      build/chatbot.min.js
```

2. From the directory containing `local/`, create the zip archive:

```
zip -r chatbot.zip local/chatbot
```

3. Upload this archive through the Moodle interface as described in the installation steps.

## Notes

This plugin does not perform any server‑side communication yet. The chat window is purely client side and always replies with “Hi there”. Add your own AJAX calls within `chatbot.js` to connect to a real chatbot service.

## Credits system

Admins can allocate chat credits to each student from the Chatbot settings page. Each message/response pair consumes one credit which is tracked in the database. When a student runs out of credits the chat window will display a warning and no further questions can be sent until more credits are added by an admin.
