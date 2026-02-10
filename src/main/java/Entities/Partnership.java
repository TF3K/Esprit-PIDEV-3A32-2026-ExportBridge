package src.main.java.Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@AllArgsConstructor
@NoArgsConstructor
public class Partnership {
    private Long id;
    private Long sourceCompanyId;
    private Long targetCompanyId;
    private PartnershipStatus status;
    private PartnershipType type;
    private LocalDateTime establishedDate;
    private LocalDateTime terminatedDate;
    private String notes;
}
