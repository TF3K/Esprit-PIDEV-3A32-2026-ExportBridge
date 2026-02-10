package src.main.java.Utils;

import at.favre.lib.crypto.bcrypt.BCrypt;

public class PasswordUtil {
    private static final int BCRYPT_COST = 16;

    public static String hashPassword(String password) {
        if (password == null || password.isEmpty()) {
            throw new IllegalArgumentException("Password cannot be null or empty");
        }

        return BCrypt.withDefaults().hashToString(BCRYPT_COST, password.toCharArray());
    }

    public static boolean verifyPw(String password, String hashedPassword) {
        if (password == null || password.isEmpty()) {
            return false;
        }
        BCrypt.Result result = BCrypt.verifyer().verify(password.toCharArray(), hashedPassword);
        return result.verified;
    }

    public static boolean needsRehash(String hashedPassword) {
        return !hashedPassword.startsWith("$2a$" + BCRYPT_COST + "$");
    }

}
