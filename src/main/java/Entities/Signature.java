package src.main.java.Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@AllArgsConstructor
@NoArgsConstructor
public class Signature {
    private Long id;
    private Long certificateId;
    private String signatoryName;
    private String signatoryTitle;
    private LocalDateTime signedDate;
    private String digitalSignature;
    private SignatureType type;
}
