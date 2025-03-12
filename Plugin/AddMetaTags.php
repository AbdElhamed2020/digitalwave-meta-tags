<?php

namespace Digitalwave\MetaTags\Plugin;

use Magento\Catalog\Helper\Product\View as ProductViewHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AddMetaTags
{
    /**
     * @var \Magento\Framework\Registry $coreRegistry ,
     */
    private $coreRegistry;

    private $resultPage;

    private $string;
    protected $scopeConfig;

    public function __construct(Registry $coreRegistry, StringUtils $string, ScopeConfigInterface $scopeConfig)
    {
        $this->coreRegistry = $coreRegistry;
        $this->string = $string;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundPrepareAndRender(
        ProductViewHelper $productViewHelper,
        callable          $proceed,
        Page              $resultPage,
        $productId,
                          $controller,
                          $params = null
    )
    {
        $enable = $this->getConfigValue('meta_tags/general_settings/enabled');
        $prefix = $this->getConfigValue('meta_tags/general_settings/product_meta_title_prefix');
        $result = $proceed($resultPage, $productId, $controller, $params);
        if ($enable && $prefix) {
            $this->resultPage = $resultPage;
            $pageConfig = $resultPage->getConfig();
            $product = $this->coreRegistry->registry('product');
            $metaTitle = $product->getMetaTitle();
            $productName = $product->getName();
            $pageConfig = $this->resultPage->getConfig();
            $metaTitle ? $pageConfig->setMetaTitle($metaTitle. " $prefix") : $pageConfig->setMetaTitle($productName. " $prefix");
        }
        return $result;
    }

    public function getConfigValue($path, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }
}
