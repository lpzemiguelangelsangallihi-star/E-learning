
package com.elearning.iop.markov;

import org.junit.jupiter.api.Test;
import org.springframework.boot.test.context.SpringBootTest;

@SpringBootTest(properties = {
    "iop.api.key=clave-exclusiva-para-pruebas"
})
class MarkovServiceApplicationTests {

    @Test
    void contextLoads() {
    }
}
