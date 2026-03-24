#!/bin/bash

# Fix ⚡admin-dashboard.blade.php
sed -i '/<<<<<<< HEAD/,/=======\|>>>>>>> feat\/admin-dashboard-redesign-20260307/d' "resources/views/components/dashboard/⚡admin-dashboard.blade.php"

# Fix sidebar.blade.php
sed -i '/<<<<<<< HEAD/,/=======\|>>>>>>> feat\/admin-dashboard-redesign-20260307/d' resources/views/layouts/app/sidebar.blade.php

# Fix header.blade.php
sed -i '/<<<<<<< HEAD/,/=======\|>>>>>>> feat\/admin-dashboard-redesign-20260307/d' resources/views/layouts/app/header.blade.php

# Fix PtspDashboardTest.php
sed -i '/<<<<<<< HEAD/,/=======\|>>>>>>> feat\/admin-dashboard-redesign-20260307/d' tests/Feature/Dashboard/PtspDashboardTest.php
