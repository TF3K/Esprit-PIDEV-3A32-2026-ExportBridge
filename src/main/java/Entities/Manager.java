package src.main.java.Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class Manager {
    private Long id;
    private String firstName;
    private String lastName;
    private Long companyId;
    private String email;
    private String password;
    private LocalDateTime createdAt;
    private LocalDateTime lastLogin;
}
