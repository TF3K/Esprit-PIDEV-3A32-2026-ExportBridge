package Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@AllArgsConstructor
@NoArgsConstructor
public class Collaboration {
    private Long id;
    private Long partnershipId;
    private String title;
    private String description;
    private LocalDateTime startDate;
    private LocalDateTime endDate;
    private CollaborationStatus status;
    private LocalDateTime createdAt;
    private LocalDateTime lastUpdated;

    public boolean isOngoing() {
        return status == CollaborationStatus.ONGOING &&
                (endDate == null || endDate.isAfter(LocalDateTime.now()));
    }
}
