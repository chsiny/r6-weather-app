import { useState, useEffect } from "react";
import { format } from "date-fns";

const cities = ["Brisbane", "Gold Coast", "Sunshine Coast"];

function App() {
  const [selectedCity, setSelectedCity] = useState("");
  const [forecast, setForecast] = useState(null);
  const [loading, setLoading] = useState(false);
  const [hasError, setHasError] = useState(false);

  useEffect(() => {
    if (selectedCity) {
      setLoading(true);
      setHasError(false);
      fetch(`http://127.0.0.1:8000/api/forecast?city=${encodeURIComponent(selectedCity)}`)
        .then((res) => {
          if (!res.ok) throw new Error("API Error");
          return res.json();
        })
        .then((data) => {
          if (!data.forecast || data.forecast.length === 0) {
            throw new Error("Empty forecast");
          }
          setForecast(data.forecast);
        })
        .catch(() => {
          setForecast(null);
          setHasError(true);
        })
        .finally(() => setLoading(false));
    } else {
      setForecast(null);
      setHasError(false);
    }
  }, [selectedCity]);


  return (
    <div className="min-h-screen bg-gray-50 flex justify-center px-4 py-16 font-sans">
      <div className="w-full max-w-5xl text-center">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">R6 Weather Forecast</h1>

        <select
          className="p-3 text-base border border-gray-300 rounded-md shadow mb-8 focus:outline-none focus:ring-2 focus:ring-blue-400"
          value={selectedCity}
          onChange={(e) => setSelectedCity(e.target.value)}
        >
          <option value="">Select a City</option>
          {cities.map((city) => (
            <option key={city} value={city}>
              {city}
            </option>
          ))}
        </select>

        {loading && <p className="text-gray-500 mb-4" role="status">Loading forecast...</p>}

        {hasError && (
          <p className="text-red-500 text-sm mt-2" role="alert">
            Unable to load forecast. Please check your internet or try a different city.
          </p>
        )}

        {forecast && (
          <div className="w-full flex justify-center">
            <div className="w-full max-w-4xl overflow-hidden rounded-xl shadow-lg bg-white">
              <div className="text-center text-lg font-semibold text-gray-800 py-4 border-b">
                {selectedCity} – 5 Day Forecast
              </div>
              <table className="w-full text-sm text-gray-700 border-separate border-spacing-0">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-4 text-left rounded-tl-xl"></th>
                    {forecast.map((_, idx) => (
                      <th
                        key={idx}
                        className={`px-6 py-4 text-center text-base font-semibold text-gray-700 ${idx === forecast.length - 1 ? "rounded-tr-xl" : ""
                          }`}
                      >
                        {format(new Date(forecast[idx].date), "EEE dd MMM")}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  <tr className="border-t">
                    <td className="px-6 py-4 text-left font-medium">Avg</td>
                    {forecast.map((day, idx) => (
                      <td key={`avg-${idx}`} className="px-6 py-4 text-center font-semibold">
                        {day.avg}°C
                      </td>
                    ))}
                  </tr>
                  <tr className="border-t">
                    <td className="px-6 py-4 text-left font-medium">Max</td>
                    {forecast.map((day, idx) => (
                      <td key={`max-${idx}`} className="px-6 py-4 text-center text-red-600 font-semibold">
                        {day.max}°C
                      </td>
                    ))}
                  </tr>
                  <tr className="border-t">
                    <td className="px-6 py-4 text-left font-medium">Low</td>
                    {forecast.map((day, idx) => (
                      <td key={`low-${idx}`} className="px-6 py-4 text-center text-blue-600 font-semibold">
                        {day.low}°C
                      </td>
                    ))}
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default App;
