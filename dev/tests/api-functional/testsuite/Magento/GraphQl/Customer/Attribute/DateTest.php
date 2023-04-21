<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\EavGraphQl\Model\Uid;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class DateTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  attributesMetadata(attributes: [{attribute_code: "%s", entity_type: "%s"}]) {
    items {
      uid
      code
      label
      entity_type
      frontend_input
      is_required
      default_value
      is_unique
    }
    errors {
      type
      message
    }
  }
}
QRY;

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'date',
                'default_value' => '2023-03-22 00:00:00'
            ],
            'attribute'
        )
    ]
    public function testMetadata(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $uid = Bootstrap::getObjectManager()->get(Uid::class)->encode(
            'customer',
            $attribute->getAttributeCode()
        );

        $result = $this->graphQlQuery(sprintf(self::QUERY, $attribute->getAttributeCode(), 'customer'));

        $this->assertEquals(
            [
                'attributesMetadata' => [
                    'items' => [
                        [
                            'uid' => $uid,
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getDefaultFrontendLabel(),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'DATE',
                            'is_required' => false,
                            'default_value' => $attribute->getDefaultValue(),
                            'is_unique' => false,
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }
}
