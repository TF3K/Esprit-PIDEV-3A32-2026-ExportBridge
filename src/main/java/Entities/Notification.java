package src.main.java.Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@AllArgsConstructor
public class Notification {
    private Long id;
    private Long managerId;
    private String type;
    private String message;
    private LocalDateTime createdAt;
    private boolean isRead;
}
