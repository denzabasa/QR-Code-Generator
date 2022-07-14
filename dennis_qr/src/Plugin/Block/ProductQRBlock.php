<?php

namespace Drupal\dennis_qr\Plugin\Block;

require __DIR__ . '/../../../vendor/autoload.php';

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'qrcode' block.
 *
 * @Block(
 *   id = "product_qr_block",
 *   admin_label = @Translation("Product QR Code block"),
 *   category = @Translation("Custom Block")
 * )
 */

class ProductQRBlock extends BlockBase {

  public function build() {

    $qrURL = $this->buildQRCode();
    return [
      '#type' => 'markup',
      '#markup' => '<div><h2>Scan here on your mobile</h2><p>To purchase this  product on our app to avail exclusive app-only</p><img src="' . $qrURL . '" />',
    ];
  }

  /**
   * Builds the QR Code.
   */
  public function buildQRCode() {
    $route_match = \Drupal::routeMatch();
    $url = FALSE;
    $writer = new PngWriter();

    if ($route_match->getRouteName() == 'entity.node.canonical') {
      $node = $route_match->getParameter('node');
      if ($node->getType() == 'product') {
        $purchaseLink = $node->get('field_purchase_link')->first()->getUrl()->toString();

        // Create QR code
        $qrCode = QrCode::create($purchaseLink)
          ->setEncoding(new Encoding('UTF-8'))
          ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
          ->setSize(300)
          ->setMargin(10)
          ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
          ->setForegroundColor(new Color(0, 0, 0))
          ->setBackgroundColor(new Color(255, 255, 255));

        $result = $writer->write($qrCode, NULL, NULL);

        $filename = 'public://qrcode_' . $node->id() . '.png';

        // Save it to a file
        $result->saveToFile($filename);
        $url = file_create_url($filename);

      }

    }

    return $url;
  }

}
