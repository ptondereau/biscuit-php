<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use Biscuit\BiscuitBundle\Attribute\BiscuitPolicy;

/**
 * Biscuit policy class.
 *
 * Use #[BiscuitPolicy] or #[IsGranted] attribute on your controllers to enforce this policy.
 */
final class <?php echo $class_name; ?>

{
    /**
     * The policy name used in configuration and attributes.
     */
    public const NAME = '<?php echo strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace('Policy', '', $class_name))); ?>';

    /**
     * The Biscuit Datalog policy string.
     *
     * Use {resource} placeholder to reference request parameters.
     *
     * @see https://www.biscuitsec.org/docs/reference/datalog/
     */
    public const POLICY = 'allow if user($id)';

    /**
     * ============================================================================
     * OPTION 1: Using #[BiscuitPolicy] attribute (Biscuit-native)
     * ============================================================================
     *
     * The #[BiscuitPolicy] attribute is a Biscuit-specific way to enforce policies.
     * Use @paramName syntax in params to reference route parameters.
     *
     * Example with static policy:
     *
     *     use Biscuit\BiscuitBundle\Attribute\BiscuitPolicy;
     *
     *     #[BiscuitPolicy(self::NAME)]
     *     public function list(): Response
     *     {
     *         // Token must satisfy the policy defined in self::POLICY
     *     }
     *
     * Example with request parameter (like Express.js scope example):
     *
     *     #[Route('/protected/{dog}', methods: ['GET'])]
     *     #[BiscuitPolicy('allow if scope({resource}, "read")', params: ['resource' => '@dog'])]
     *     public function show(string $dog): Response
     *     {
     *         // @dog references the route parameter
     *         // Token must contain: scope("puna", "read") to access /protected/puna
     *     }
     *
     * ============================================================================
     * OPTION 2: Using #[IsGranted] attribute (Symfony-native)
     * ============================================================================
     *
     * Use Symfony's standard #[IsGranted] with the subject parameter.
     * The subject value replaces {resource} in the policy.
     *
     * Example with inline policy:
     *
     *     use Symfony\Component\Security\Http\Attribute\IsGranted;
     *
     *     #[Route('/protected/{dog}', methods: ['GET'])]
     *     #[IsGranted('allow if scope({resource}, "read")', subject: 'dog')]
     *     public function show(string $dog): Response
     *     {
     *         // The $dog parameter value replaces {resource} in the policy
     *     }
     *
     * Example with named policy:
     *
     *     #[IsGranted(self::NAME, subject: 'dog')]
     *     public function show(string $dog): Response
     *
     * ============================================================================
     * Configuration
     * ============================================================================
     *
     * Register policies in biscuit.yaml:
     *
     *     # config/packages/biscuit.yaml
     *     biscuit:
     *         policies:
     *             scope_read: 'allow if scope({resource}, "read")'
     *
     * Create token templates:
     *
     *     biscuit:
     *         token_templates:
     *             dog_reader:
     *                 facts:
     *                     - 'scope({dog}, "read")'
     *
     * Create a token:
     *
     *     bin/console biscuit:token:create dog_reader --param dog=puna
     */
}
