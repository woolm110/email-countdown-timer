<?php

include 'GIFEncoder.class.php';

/**
 * Class CountdownTimer
 */
class CountdownTimer
{

  /**
   * @var object
   */
  private $base;

  /**
   * @var object
   */
  private $box;

  /**
   * @var int
   */
  private $width = 0;

  /**
   * @var int
   */
  private $height = 0;

  /**
   * @var int
   */
  private $xOffset = 0;

  /**
   * @var int
   */
  private $yOffset = 0;

  /**
   * @var int
   */
  private $delay = 100;

  /**
   * @var array
   */
  private $frames = array();

  /**
   * @var array
   */
  private $delays = array();

  /**
   * @var array
   */
  private $date = array();

  /**
   * @var array
   */
  private $fontSettings = array();

  /**
   * @var array
   */
  private $boundingBox = array();

  /**
   * @var string
   */
  private $fontPath = 'fonts/';

  /**
   * @var int
   */
  private $seconds = 30;

  /**
   * hex2rgb
   * Convert a hex
   * colour to rgb
   * @param  string $hex
   * @return array
   */
  private function hex2rgb($hex)
  {
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) == 3) {
      $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
      $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
      $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = array($r, $g, $b);

    return $rgb;
  }

  /**
   * createFilledBox
   * Create a filled box
   * to use at the base
   * @param  $image
   */
  private function createFilledBox($image)
  {
    imagefilledrectangle(
      $image,
      0,
      0,
      $this->width,
      $this->height,
      imagecolorallocate(
        $image,
        $this->boxColor[0],
        $this->boxColor[1],
        $this->boxColor[2]
      )
    );
  }

  /**
   * CountdownTimer constructor.
   *
   * @param $settings
   */
  public function __construct($settings)
  {
    $this->width = $settings['width'];
    $this->height = $settings['height'];
    $this->boxColor = $settings['boxColor'];
    $this->xOffset = $settings['xOffset'];
    $this->yOffset = $settings['yOffset'];
    $this->boxColor = $this->hex2rgb($settings['boxColor']);
    $this->fontColor = $this->hex2rgb($settings['fontColor']);

    $this->labelOffsets = explode(',', $settings['labelOffsets']);

    $this->date['time'] = $settings['time'];
    $this->date['futureDate'] = new DateTime(date('r', strtotime($settings['time'])));
    $this->date['timeNow'] = time();
    $this->date['now'] = new DateTime(date('r', time()));

    // create new images
    $this->box = imagecreatetruecolor($this->width, $this->height);
    $this->base = imagecreatetruecolor($this->width, $this->height);

    $this->fontSettings['path'] = $this->fontPath . $settings['font'] . '.ttf';
    $this->fontSettings['color'] = imagecolorallocate($this->box, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);
    $this->fontSettings['size'] = $settings['fontSize'];
    $this->fontSettings['characterWidth'] = imagefontwidth($this->fontSettings['path']);

    // get the width of each character
    $string = "0:";
    $size = $this->fontSettings['size'];
    $angle = 0;
    $fontfile = $this->fontSettings['path'];

    $strlen = strlen($string);
    for ($i = 0; $i < $strlen; $i++) {
      $dimensions = imagettfbbox($size, $angle, $fontfile, $string[$i]);
      $this->fontSettings['characterWidths'][] = array(
        $string[i] => $dimensions[2]
      );
    }

    $this->images = array(
      'box' => $this->box,
      'base' => $this->base,
    );

    // create empty filled rectangles
    foreach ($this->images as $image) {
      $this->createFilledBox($image);
    }

    $this->createFrames();
  }

  /**
   * createFrames
   * Create all of the frames for 
   * the countdown timer
   * @return void
   */
  public function createFrames()
  {
    $this->boundingBox = imagettfbbox($this->fontSettings['size'], 0, $this->fontSettings['path'], '00:00:00:00');
    $this->characterDimensions = imagettfbbox($this->fontSettings['size'], 0, $this->fontSettings['path'], '0');
    $this->characterWidth = $this->characterDimensions[2];
    $this->characterHeight = abs($this->characterDimensions[1] + $this->characterDimensions[7]);

    $this->base = $this->applyTextToImage($this->base, $this->fontSettings, $this->date);

    // create each frame
    for ($i = 0; $i <= $this->seconds; $i++) {
      $layer = imagecreatetruecolor($this->width, $this->height);
      $this->createFilledBox($layer);

      $layer = $this->applyTextToImage($layer, $this->fontSettings, $this->date);
    }

    $this->showImage();
  }

  /**
   * applyTextToImage
   * Apply each time stamp
   * to the image
   * @param $image
   * @param $font
   * @param $date
   * @return mixed
   */
  private function applyTextToImage($image, $font, $date)
  {
    $interval = date_diff(
      $date['futureDate'],
      $date['now']
    );

    if ($date['futureDate'] < $date['now']) {
      $text = $interval->format('00:00:00:00');
      $this->loops = 1;
    } else {
      $text = $interval->format('0%a:%H:%I:%S');
      $this->loops = 0;
    }

    $labels = array('Days', 'Hrs', 'Mins', 'Secs');

    // apply the labels to the image $this->yOffset + ($this->characterHeight * 0.8)
    foreach ($labels as $key => $label) {
      imagettftext($image, 15, 0, $this->xOffset + ($this->characterWidth * $this->labelOffsets[$key]), 98, $font['color'], $font['path'], $label);
    }

    // apply time to new image
    imagettftext($image, $font['size'], 0, $this->xOffset, $this->yOffset, $font['color'], $font['path'], $text);

    ob_start();
    imagegif($image);
    $this->frames[] = ob_get_contents();
    $this->delays[] = $this->delay;
    ob_end_clean();

    $this->date['now']->modify('+1 second');

    return $image;
  }

  /**
   * showImage
   * Create the animated gif
   * @return void
   */
  public function showImage()
  {
    $gif = new AnimatedGif($this->frames, $this->delays, $this->loops);
    $gif->display();
  }
}

/**
 * Create a new countdown
 */
new CountdownTimer(array(
  'time' => $_GET['time'],
  'width' => isset($_GET['width']) ? $_GET['width'] : 640,
  'height' => isset($_GET['height']) ? $_GET['height'] : 110,
  'boxColor' => isset($_GET['boxColor']) ? $_GET['boxColor'] : '#000',
  'font' => isset($_GET['font']) ? $_GET['font'] : 'BebasNeue',
  'fontColor' => isset($_GET['fontColor']) ? $_GET['fontColor'] : '#fff',
  'fontSize' => isset($_GET['fontSize']) ? $_GET['fontSize'] : 60,
  'xOffset' => isset($_GET['xOffset']) ? $_GET['xOffset'] : 155,
  'yOffset' => isset($_GET['yOffset']) ? $_GET['yOffset'] : 70,
  'labelOffsets' => isset($_GET['labelOffsets']) ? $_GET['labelOffsets'] : "1.4,5,8,11",
));

// http://[server-address]/countdown.php?time=2016-12-25+00:00:01&width=640&height=110&boxColor=8B2860&font=BebasNeue&fontColor=FBB92C&fontSize=60&xOffset=155&yOffset=70&labelOffsets=1.4,5,8,11
