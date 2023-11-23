<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

class Paysites
{
    /**
     * @param string $siteId
     * @return bool
     */
    public static function checkIfPaysites(string $siteId): bool
    {
        $paysitesSites = [
            "018047dc-cbce-4de6-aec1-32260d793398",
            "05521e01-157d-459b-8cd3-d65bb22fadc4",
            "0d754137-dfd4-49d7-9e8f-492dcbccf032",
            "0eb35ebf-2add-4696-9a6e-f55726878f49",
            "10d55bf3-b7f4-4d02-948d-056c94e833ed",
            "13716312-ac03-4889-aa72-2270b29d47ac",
            "152bb43c-bd7b-4634-bbfb-27de2b8f090d",
            "16f29549-0396-4043-8ec5-1fada796b393",
            "1d08cdb8-c857-40b1-8e32-fd6815405db2",
            "1f089f56-71b1-46fc-bd47-30b065b50927",
            "2021c3a0-cd64-4a94-b0f7-7f5d5af5a943",
            "23541e5b-e800-43f0-b0e4-73073da924e2",
            "299d3006-cf3d-11e9-8c91-0cc47a283dd2",
            "299d3e6b-cf3d-11e9-8c91-0cc47a283dd2",
            "299f8344-cf3d-11e9-8c91-0cc47a283dd2",
            "299f84c9-cf3d-11e9-8c91-0cc47a283dd2",
            "299f8653-cf3d-11e9-8c91-0cc47a283dd2",
            "299f9d47-cf3d-11e9-8c91-0cc47a283dd2",
            "29a1ee81-cf3d-11e9-8c91-0cc47a283dd2",
            "29a4e719-cf3d-11e9-8c91-0cc47a283dd2",
            "2effadc9-1187-474d-9d48-3aa77ecc447c",
            "364c4623-4e5c-4a31-ae4f-759371f1d05b",
            "37049ca5-4cc4-45cf-9561-a36d44956086",
            "38346264-816a-4d5a-9392-0a2da80bee22",
            "392f814b-25bc-41ec-b224-a036505f246f",
            "3f86c5f6-6caa-4d99-878c-ac9707da33fe",
            "42dd8d7d-0294-47de-8eda-fc6abaae0d7e",
            "4370d8d8-f588-4aee-a59a-10f564e6048a",
            "4749be98-97b6-4381-90d8-4aedd994b195",
            "4c22fba2-f883-11e8-8eb2-f2801f1b9fd1",
            "4ef43642-5ac7-42d0-b8e5-c427751889e7",
            "500fd0ef-98a5-4b3c-b5df-92b0e36cf654",
            "56ea6810-d2be-4315-b754-f4399c1ba44e",
            "56fea808-fd05-457e-a9aa-676fc88aeede",
            "5839147d-94cf-4349-8c6a-21b8c8612373",
            "5a00c0b3-9430-471b-8f2f-3c399ba20a0f",
            "6128e740-2a61-43e0-b717-c9976b4ec3c5",
            "61c52c1e-3160-403a-9fda-6135bfbb2639",
            "62724e49-3446-407f-8a06-dad23b1aa1ab",
            "6677383d-9c94-4afd-966c-fad5ced1ee09",
            "6e353aec-d696-4f0c-b02b-b86c92e734bf",
            "7078e6e3-b066-416c-aa58-dd8e3418eef4",
            "71506d7d-55e6-4fc2-bd54-af8e39da7423",
            "72422e12-f2f9-4de3-8237-26f47801f048",
            "73de97e2-265c-43cf-ab7b-35ff125537f5",
            "7b122b14-2e9b-4bd7-9430-faf2f5a7375b",
            "822f86af-92cc-4da0-9455-8f451d9481f1",
            "86103bda-5765-4ddc-82fd-61a5883c185c",
            "8e34c94e-135f-4acb-9141-58b3a6e56c74",
            "913a0ed9-68f4-4fe0-8306-595a63ae3274",
            "94628566-7a96-46a3-80b6-c0c16f4d8da6",
            "98ff56df-9231-4848-ab42-58e75b02a830",
            "99afc0fb-7fdf-4a1d-98b8-3255ca65aa85",
            "9c26ec4b-f211-47e9-b5f7-22c0a170a21a",
            "a503f4bd-abff-4b85-bb32-b02081ae65e1",
            "ab045cb8-ca11-43f1-abea-37f79b54bd56",
            "adcc63ee-d7f5-404a-a6dd-6da1298dd939",
            "b598f225-e2e8-4caf-a572-7340951ffec7",
            "bd396a9f-d374-4b87-85ff-68e6f838f161",
            "bd6ce957-ef64-4d4e-8154-f2e69a7c7e6f",
            "bde7816d-1c44-4343-8339-70e1f45be704",
            "c2e2713f-6d1d-410d-941f-94c5aa7b8b0c",
            "c2e52e3b-2d18-4e81-adf2-280c31728373",
            "d06f5cc8-4651-445c-baca-22ddcd383f0e",
            "d508271a-44d1-4b50-9fac-775e4ce4fb22",
            "e3c43444-8275-45cd-b744-898580229e42",
            "e5c477f9-2656-4dd7-b78c-13a11426a706",
            "eef69ce5-8a62-4d86-afee-e91492227b23",
            "f1c3baef-3271-4ae8-b236-52dd2760ad38",
            "f213032e-332a-409c-aaaf-05cb41653119",
            "f2e55452-927d-43f9-a8a4-6b874cfbede1",
            "f6fe7a6c-47b7-4443-9e7b-f2775e1f448d",
            "f80e66bd-6fe9-46d0-9318-cd630fa3ccec"
        ];

        if (in_array($siteId, $paysitesSites)) {
            return true;
        }

        return false;
    }
}