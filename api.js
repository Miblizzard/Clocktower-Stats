
      /* exported gapiLoaded */
      /* exported gisLoaded */
      /* exported handleAuthClick */
      /* exported handleSignoutClick */

      
      const API_KEY = '';
      const ID = '';
      const SHEET_NAME = '';

    async function getData() {
        const url = "https://sheets.googleapis.com/v4/spreadsheets/" + ID + "/values/" + SHEET_NAME + "?key=" + API_KEY;
        try {
            const response = await fetch(url); 
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            return result = await response.json(); // turns the fetched response into a json structure
            
        } catch (error) {
            console.error(error.message);
        }
    }
