<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

/**
 * Biscuit policy class.
 *
 * Use the #[IsGranted] attribute on your controllers to enforce this policy.
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
     * Using #[IsGranted] attribute
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
