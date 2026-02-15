import Entities.Manager;
import Utils.AppState;
import Controllers.AuthenticationController;

import org.junit.jupiter.api.*;
import static org.junit.jupiter.api.Assertions.*;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class AuthControllerTest {

    private static AuthenticationController AuthenticationController;
    private static String testEmail;

    @BeforeAll
    public static void setup() {
        AuthenticationController = new Controllers.AuthenticationController();
        testEmail = "test_" + System.currentTimeMillis() + "@example.com";
    }

    @AfterAll
    public static void cleanup() {
        AppState.clearSession();
    }

    @Test
    @Order(1)
    @DisplayName("Test 1: Register New Manager")
    public void testRegisterNewManager() {
        boolean result = AuthenticationController.register(
                "Test",
                "User",
                testEmail,
                "Password123!",
                "Password123!",
                null
        );

        assertTrue(result, "Registration should succeed");
    }

    @Test
    @Order(2)
    @DisplayName("Test 2: Register with Duplicate Email")
    public void testRegisterDuplicateEmail() {

        boolean result = AuthenticationController.register(
                "Another",
                "User",
                testEmail,
                "Password123!",
                "Password123!",
                null
        );

        assertFalse(result, "Registration should fail for duplicate email");
    }

    @Test
    @Order(3)
    @DisplayName("Test 3: Register with Mismatched Passwords")
    public void testRegisterPasswordMismatch() {
        boolean result = AuthenticationController.register(
                "Test",
                "User",
                "mismatch@example.com",
                "Password123!",
                "DifferentPassword123!",
                null
        );

        assertFalse(result, "Registration should fail for mismatched passwords");
    }

    @Test
    @Order(4)
    @DisplayName("Test 4: Login with Valid Credentials")
    public void testLoginSuccess() {
        String token = AuthenticationController.login(testEmail, "Password123!", true);

        assertNotNull(token, "Login should return a token");
        assertTrue(token.length() > 0, "Token should not be empty");
        assertTrue(AuthenticationController.isLoggedIn(), "User should be logged in");

        Manager currentManager = AuthenticationController.getCurrentManager();
        assertNotNull(currentManager, "Current manager should be set");
        assertEquals(testEmail, currentManager.getEmail(), "Manager email should match");
    }

    @Test
    @Order(5)
    @DisplayName("Test 5: Login with Invalid Password")
    public void testLoginInvalidPassword() {

        String token = AuthenticationController.login(testEmail, "WrongPassword123!", true);

        assertNull(token, "Login should fail with wrong password");
    }

    @Test
    @Order(6)
    @DisplayName("Test 6: Login with Non-existent Email")
    public void testLoginNonExistentUser() {
        String token = AuthenticationController.login("nonexistent@example.com", "Password123!", true);

        assertNull(token, "Login should fail for non-existent user");
    }

    @Test
    @Order(7)
    @DisplayName("Test 7: Validate Session")
    public void testValidateSession() {
        AuthenticationController.login(testEmail, "Password123!" , true);

        boolean isValid = AuthenticationController.validateSession();
        assertTrue(isValid, "Session should be valid after login");
    }

    @Test
    @Order(8)
    @DisplayName("Test 8: Logout")
    public void testLogout() {
        AuthenticationController.login(testEmail, "Password123!", true);
        assertTrue(AuthenticationController.isLoggedIn(), "Should be logged in");

        AuthenticationController.logout();
        assertFalse(AuthenticationController.isLoggedIn(), "Should be logged out");
    }

    @Test
    @Order(9)
    @DisplayName("Test 9: Change Password")
    public void testChangePassword() {
        AuthenticationController.login(testEmail, "Password123!", true);

        boolean result = AuthenticationController.changePassword(
                "Password123!",
                "NewPassword456!",
                "NewPassword456!"
        );

        assertTrue(result, "Password change should succeed");

        AuthenticationController.logout();
        String token = AuthenticationController.login(testEmail, "NewPassword456!", true);

        assertNotNull(token, "Login with new password should succeed");
    }

    @Test
    @Order(10)
    @DisplayName("Test 10: Change Password with Wrong Old Password")
    public void testChangePasswordWrongOld() {
        AuthenticationController.login(testEmail, "NewPassword456!", true);

        boolean result = AuthenticationController.changePassword(
                "WrongOldPassword!",
                "AnotherPassword789!",
                "AnotherPassword789!"
        );

        assertFalse(result, "Password change should fail with wrong old password");
    }
}