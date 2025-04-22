# Energy Monitor Pro

A modern web application for monitoring and analyzing energy-related data including temperature, solar radiation, and wind conditions. Built with Laravel and modern web technologies.

## Features

- **Real-time Data Monitoring**
  - Temperature tracking
  - Solar radiation analysis
  - Wind speed and direction monitoring
  - Current conditions display

- **Interactive Location Selection**
  - Map-based location picking
  - Current location detection
  - Location search functionality
  - Reverse geocoding

- **Data Visualization**
  - Temperature trends
  - Solar radiation analysis
  - Wind analysis
  - 7-day forecast
  - Historical data charts

- **Responsive Design**
  - Mobile-friendly interface
  - Adaptive layouts
  - Touch-friendly controls
  - Modern UI/UX

## Technology Stack

### Backend
- **Laravel** - PHP web framework
- **OpenStreetMap API** - For location data and geocoding
- **Weather API** - For weather and energy-related data

### Frontend
- **Tailwind CSS** - Utility-first CSS framework
- **Chart.js** - For data visualization
- **Leaflet.js** - For interactive maps
- **Font Awesome** - For icons
- **Lottie Files** - For animations

### Key Libraries
- **Chart.js** - Interactive charts and graphs
- **ApexCharts** - Advanced charting library
- **Leaflet** - Open-source mapping library

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=energy_monitor
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. Start the development server:
```bash
php artisan serve
```

## API Integration

The application integrates with several APIs:

- **OpenStreetMap Nominatim API** - For location search and reverse geocoding
- **Weather API** - For weather and energy data
- **Geolocation API** - For current location detection

## Project Structure

```
energy-monitor/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── EnergyController.php
│   └── Models/
├── resources/
│   └── views/
│       ├── energy/
│       │   └── index.blade.php
│       └── location-modal.blade.php
├── public/
│   └── assets/
├── routes/
│   └── web.php
└── config/
```

## Features in Detail

### Location Management
- Interactive map interface
- Location search with autocomplete
- Current location detection
- Reverse geocoding for location names

### Data Visualization
- Real-time temperature charts
- Solar radiation analysis graphs
- Wind speed and direction visualization
- Historical data trends
- 7-day forecast display

### User Interface
- Responsive design for all devices
- Dark mode interface
- Animated transitions
- Interactive charts and graphs
- Mobile-friendly navigation

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- OpenStreetMap for mapping data
- Weather API providers
- All contributors and maintainers

## Support

For support, please open an issue in the repository or contact the maintainers.
