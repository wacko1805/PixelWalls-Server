<?php
if(isset($_POST['generate'])){
    $repoOwner = "wacko1805";
    $repoName = "Google-Pixel-Wallpapers-images";
    $baseUrl = "https://raw.githubusercontent.com/$repoOwner/$repoName/main/";
    $images = array();

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.github.com/repos/$repoOwner/$repoName/contents",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            "Accept: application/vnd.github.v3+json",
            "User-Agent: My PHP Script",
            "Authorization: AUTH_ID"
        )
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $files = json_decode($response, true);

        foreach ($files as $file) {
            if ($file["type"] === "dir") {
                $subfolderName = str_replace("-", " ", $file["name"]);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.github.com/repos/$repoOwner/$repoName/contents/" . $file["path"],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => array(
                        "Accept: application/vnd.github.v3+json",
                        "User-Agent: My PHP Script",
                        "Authorization: AUTH_ID"
                    )
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $subfolderFiles = json_decode($response, true);

                if ($subfolderFiles != null) {
                    foreach ($subfolderFiles as $subfolderFile) {
                        if ($subfolderFile["type"] === "file" && preg_match('/\.(jpg|jpeg|png)$/i', $subfolderFile["name"])) {
                            $imageName = pathinfo($subfolderFile["name"], PATHINFO_FILENAME);
                            $imagePath = $baseUrl . $subfolderFile["path"];
                            $imageCollection = str_replace("-", " ", $subfolderName);

                            $imageData = array(
                                "name" => $imageName,
                                "url" => $imagePath,
                                "collections" => $imageCollection
                            );

                            array_push($images, $imageData);
                        }
                    }
                }
            }
        }

        if (!empty($images)) {
            $jsonData = json_encode($images, JSON_PRETTY_PRINT);
            file_put_contents('wallpapers.json', $jsonData);
            echo "JSON data written to file.";
        } else {
            echo "No images found in repository.";
        }
    }
}
?>

<form method="POST">
    <button type="submit" name="generate">Generate JSON file</button>
</form>