# Email Countdown Timer

> Create an animated countdown timer for use within HTML emails

## Getting started
- Upload files to server

## Usage
- Navigate to your script in the browser and append the time you want to countdown to in the querystring parameter `time`. e.g. `http://[server-address]/countdown.php?time=2016-12-25+00:00:01`.

To include the countdown timer in your HTML email you simply need to create an image tag and in the `src` set it to the browser address. *Note: Animated gifs are not supported in Outlook and for these the first frame will be shown*.

## Settings

The countdown timer can be customised to fit your style. The follow can be modified using query string parameters.
- time
- width
- height
- boxColor
- font
- fontColor
- fontSize
- xOffset
- yOffset
- labelOffsets

An example of this would be `http://[server-address]/countdown.php?time=2016-12-25+00:00:01&width=640&height=110&boxColor=8B2860&font=BebasNeue&fontColor=FBB92C&fontSize=60&xOffset=155&yOffset=70&labelOffsets=1.4,5,8,11`.

### Fonts

Any font file can be used as the base font for the countdowm timer. To use a custom font you'll need to upload it to the `fonts` directory and reference the exact name in the query string parameter `font`. *Note: fonts must be uploaded using the `ttf` file extension*.
