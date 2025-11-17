<?php
/**
 * Auto Disable Stock Zero Module
 *
 * Automatically disables products when their total available stock reaches 0.
 * Does NOT automatically re-enable products when stock is added back.
 *
 * @author Paul Bihr
 * @copyright 2025 Paul Bihr
 * @license MIT
 * @version 1.1.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AutoDisableStockZero
 *
 * PrestaShop module that automatically sets products to inactive when stock reaches zero.
 * Compatible with PrestaShop 1.7.7.8 and PHP 7.3.x
 */
class AutoDisableStockZero extends Module
{
    /**
     * Module constructor
     */
    public function __construct()
    {
        $this->name = 'autodisablestockzero';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Paul Bihr';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Auto Disable Stock Zero');
        $this->description = $this->l('Automatically disables products when their stock reaches 0.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    /**
     * Install the module
     *
     * @return bool True if installation succeeded, false otherwise
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        // Register hook that triggers when stock quantity is updated
        if (!$this->registerHook('actionUpdateQuantity')) {
            return false;
        }

        // Process existing products with zero stock on installation
        $this->processExistingProducts();

        // Log successful installation
        PrestaShopLogger::addLog(
            sprintf('[%s] Module installed successfully', $this->name),
            1,
            null,
            'Module',
            null,
            true
        );

        return true;
    }

    /**
     * Uninstall the module
     *
     * @return bool True if uninstallation succeeded, false otherwise
     */
    public function uninstall()
    {
        // Log uninstallation
        PrestaShopLogger::addLog(
            sprintf('[%s] Module uninstalled', $this->name),
            1,
            null,
            'Module',
            null,
            true
        );

        return parent::uninstall();
    }

    /**
     * Process all existing products and disable those with zero stock
     *
     * This method is called during module installation to ensure that
     * all products already in stock zero are disabled immediately.
     * It scans all active products in the current shop and disables
     * those with total stock <= 0.
     *
     * @return void
     */
    private function processExistingProducts()
    {
        try {
            // Get current shop context
            $idShop = (int) Context::getContext()->shop->id;

            // SQL query to get all active products with their stock
            // We only select products that are currently active
            $sql = new DbQuery();
            $sql->select('p.id_product');
            $sql->from('product', 'p');
            $sql->innerJoin(
                'product_shop',
                'ps',
                'p.id_product = ps.id_product AND ps.id_shop = ' . (int) $idShop
            );
            $sql->where('ps.active = 1');

            // Execute query
            $products = Db::getInstance()->executeS($sql);

            if (!$products || !is_array($products)) {
                PrestaShopLogger::addLog(
                    sprintf('[%s] No products found to process', $this->name),
                    1,
                    null,
                    'Module',
                    null,
                    true
                );
                return;
            }

            $processedCount = 0;
            $disabledCount = 0;

            // Process each product
            foreach ($products as $product) {
                $idProduct = (int) $product['id_product'];
                $processedCount++;

                // Get total stock for the product (all combinations)
                $totalQuantity = StockAvailable::getQuantityAvailableByProduct(
                    $idProduct,
                    0,
                    $idShop
                );

                // If stock is zero or negative, disable the product
                if ($totalQuantity <= 0) {
                    $this->disableProduct($idProduct, $idShop, $totalQuantity);
                    $disabledCount++;
                }
            }

            // Log summary
            PrestaShopLogger::addLog(
                sprintf(
                    '[%s] Processed %d existing products, disabled %d products with zero stock',
                    $this->name,
                    $processedCount,
                    $disabledCount
                ),
                1,
                null,
                'Module',
                null,
                true
            );
        } catch (Exception $e) {
            // Log any exception that occurs
            PrestaShopLogger::addLog(
                sprintf(
                    '[%s] Error processing existing products: %s',
                    $this->name,
                    $e->getMessage()
                ),
                3,
                $e->getCode(),
                'Module',
                null,
                true
            );
        }
    }

    /**
     * Hook executed when product quantity is updated
     *
     * This hook is triggered whenever stock quantity changes.
     * It checks if the total available stock for the product has reached 0,
     * and if so, disables the product.
     *
     * @param array $params Hook parameters containing:
     *                      - id_product: Product ID
     *                      - id_product_attribute: Combination ID (0 for simple products)
     *
     * @return void
     */
    public function hookActionUpdateQuantity($params)
    {
        try {
            // Extract product information from hook parameters
            $idProduct = (int) $params['id_product'];
            $idProductAttribute = isset($params['id_product_attribute'])
                ? (int) $params['id_product_attribute']
                : 0;

            // Validate product ID
            if (!$idProduct || $idProduct <= 0) {
                PrestaShopLogger::addLog(
                    sprintf('[%s] Invalid product ID in hook params', $this->name),
                    2,
                    null,
                    'Module',
                    null,
                    true
                );
                return;
            }

            // Get current shop context
            $idShop = (int) Context::getContext()->shop->id;

            // Calculate total available quantity for the product
            // Using 0 as second parameter gets the total across all combinations
            $totalQuantity = StockAvailable::getQuantityAvailableByProduct(
                $idProduct,
                0,
                $idShop
            );

            // Only disable product if stock is 0 or negative
            // Do NOT re-enable if stock becomes positive (manual action required)
            if ($totalQuantity <= 0) {
                $this->disableProduct($idProduct, $idShop, $totalQuantity);
            }
        } catch (Exception $e) {
            // Log any exception that occurs
            PrestaShopLogger::addLog(
                sprintf(
                    '[%s] Error in hookActionUpdateQuantity: %s',
                    $this->name,
                    $e->getMessage()
                ),
                3,
                $e->getCode(),
                'Module',
                $idProduct,
                true
            );
        }
    }

    /**
     * Disable a product when its stock reaches zero
     *
     * @param int $idProduct Product ID to disable
     * @param int $idShop Shop ID for context
     * @param int $currentStock Current stock quantity (for logging)
     *
     * @return void
     */
    private function disableProduct($idProduct, $idShop, $currentStock)
    {
        try {
            // Load the product object
            $product = new Product($idProduct, false, null, $idShop);

            // Verify product exists
            if (!Validate::isLoadedObject($product)) {
                PrestaShopLogger::addLog(
                    sprintf(
                        '[%s] Product ID %d not found',
                        $this->name,
                        $idProduct
                    ),
                    2,
                    null,
                    'Product',
                    $idProduct,
                    true
                );
                return;
            }

            // Check if product is already disabled
            if (!$product->active) {
                // Product is already disabled, nothing to do
                return;
            }

            // Disable the product
            $product->active = 0;

            // Update product in database
            // Use multishop context to only update the current shop
            if ($product->update()) {
                // Log successful disabling
                PrestaShopLogger::addLog(
                    sprintf(
                        '[%s] Product ID %d disabled (stock: %d, shop: %d)',
                        $this->name,
                        $idProduct,
                        $currentStock,
                        $idShop
                    ),
                    1,
                    null,
                    'Product',
                    $idProduct,
                    true
                );
            } else {
                // Log failure to update
                PrestaShopLogger::addLog(
                    sprintf(
                        '[%s] Failed to disable product ID %d',
                        $this->name,
                        $idProduct
                    ),
                    3,
                    null,
                    'Product',
                    $idProduct,
                    true
                );
            }
        } catch (Exception $e) {
            // Log any exception during product update
            PrestaShopLogger::addLog(
                sprintf(
                    '[%s] Error disabling product ID %d: %s',
                    $this->name,
                    $idProduct,
                    $e->getMessage()
                ),
                3,
                $e->getCode(),
                'Product',
                $idProduct,
                true
            );
        }
    }
}
