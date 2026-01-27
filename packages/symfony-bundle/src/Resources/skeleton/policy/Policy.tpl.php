<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use Biscuit\BiscuitBundle\Attribute\BiscuitPolicy;

/**
 * Biscuit policy class.
 *
 * Use the #[BiscuitPolicy] attribute on your controllers to enforce this policy.
 */
final class <?php echo $class_name; ?>

{
    /**
     * The policy name used in configuration and attributes.
     */
    public const NAME = '<?php echo strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace('Policy', '', $class_name))); ?>';

    /**
     * The Biscuit policy string.
     *
     * @see https://www.biscuitsec.org/docs/reference/datalog/
     */
    public const POLICY = 'allow if user($id)';

    /**
     * Example usage with the #[BiscuitPolicy] attribute:
     *
     * #[BiscuitPolicy(self::POLICY)]
     * public function myAction(): Response
     * {
     *     // ...
     * }
     *
     * Or with named policy from configuration:
     *
     * #[BiscuitPolicy(self::NAME)]
     * public function myAction(): Response
     * {
     *     // ...
     * }
     */
}
