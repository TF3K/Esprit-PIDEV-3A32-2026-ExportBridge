import org.junit.jupiter.api.Test;
import Utils.PasswordUtil;
import org.junit.jupiter.api.BeforeEach;
import static org.junit.jupiter.api.Assertions.*;

public class PasswordUtilTest {
    private String password;
    @BeforeEach
    public void setUp() {
        password = "SomeSecureAndSecretPassword";
    }

    @Test
    public void testPasswordHashing() {
        String hashedPassword = PasswordUtil.hashPassword(password);

        assertNotEquals(password,hashedPassword);
        assertTrue(hashedPassword.startsWith("$2a$"));
        assertEquals(60, hashedPassword.length());
    }

    @Test
    public void testPasswordVerification() {
        String hashedPassword = PasswordUtil.hashPassword(password);

        assertTrue(PasswordUtil.verifyPw(password,hashedPassword));

        assertFalse(PasswordUtil.verifyPw("SomeOtherPasswordThatIsNotRight",hashedPassword));
        assertFalse(PasswordUtil.verifyPw("SomeOtherPasswordThatIsALSONotRight",password));
    }

    @Test
    public void testSamePasswordDifferentHash() {
        String hashedPassword1 = PasswordUtil.hashPassword(password);
        String hashedPassword2 = PasswordUtil.hashPassword(password);

        assertNotEquals(hashedPassword1,hashedPassword2);

        assertTrue(PasswordUtil.verifyPw(password,hashedPassword1));
        assertTrue(PasswordUtil.verifyPw(password,hashedPassword2));
    }

    @Test
    public void testNullPassword() {
        assertThrows(IllegalArgumentException.class, () -> {
            PasswordUtil.hashPassword(null);
        });

        assertFalse(PasswordUtil.verifyPw("","SomeHashedPassword"));
    }

    @Test
    public void testEmptyPassword() {
        assertThrows(IllegalArgumentException.class, () -> {
            PasswordUtil.hashPassword("");
        });
    }
}
