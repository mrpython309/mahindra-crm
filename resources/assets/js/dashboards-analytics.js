/**
 * Dashboard Analytics — Lead Management Charts
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const cardColor    = config.colors.cardColor;
  const headingColor = config.colors.headingColor;
  const legendColor  = config.colors.bodyColor;
  const labelColor   = config.colors.textMuted;
  const borderColor  = config.colors.borderColor;
  const fontFamily   = config.fontFamily;

  const data = window.dashboardData || {};

  // ── 1. Leads by Month — Bar Chart ────────────────────────────────────────
  const leadsMonthlyEl = document.querySelector('#leadsMonthlyChart');
  if (leadsMonthlyEl) {
    new ApexCharts(leadsMonthlyEl, {
      series: [{
        name: 'Leads',
        data: data.leadsMonthly?.data ?? []
      }],
      chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          columnWidth: '40%',
          borderRadius: 6,
          borderRadiusApplication: 'end'
        }
      },
      colors: [config.colors.primary],
      dataLabels: { enabled: false },
      grid: {
        strokeDashArray: 6,
        borderColor: borderColor,
        padding: { top: 0, bottom: -8, left: 10, right: 10 }
      },
      xaxis: {
        categories: data.leadsMonthly?.labels ?? [],
        labels: {
          style: { fontSize: '13px', fontFamily, colors: labelColor }
        },
        axisTicks: { show: false },
        axisBorder: { show: false }
      },
      yaxis: {
        labels: {
          style: { fontSize: '13px', fontFamily, colors: labelColor }
        }
      },
      tooltip: {
        y: { formatter: val => val + ' leads' }
      },
      states: {
        hover:  { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
      }
    }).render();
  }

  // ── 2. Leads by Status — Donut Chart ─────────────────────────────────────
  const leadsByStatusEl = document.querySelector('#leadsByStatusChart');
  if (leadsByStatusEl) {
    const statusLabels = data.leadsByStatus?.labels ?? [];
    const statusData   = data.leadsByStatus?.data   ?? [];

    new ApexCharts(leadsByStatusEl, {
      series: statusData,
      labels: statusLabels,
      chart: {
        type: 'donut',
        height: 280
      },
      colors: [
        config.colors.primary,
        config.colors.success,
        config.colors.warning,
        config.colors.danger,
        config.colors.info,
        config.colors.secondary
      ],
      stroke: { width: 4, colors: [cardColor] },
      dataLabels: { enabled: false },
      legend: {
        show: true,
        position: 'bottom',
        fontFamily,
        fontSize: '13px',
        labels: { colors: legendColor }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '68%',
            labels: {
              show: true,
              name: {
                show: true,
                fontSize: '13px',
                fontFamily,
                color: legendColor,
                offsetY: -10
              },
              value: {
                show: true,
                fontSize: '1.125rem',
                fontFamily,
                fontWeight: 500,
                color: headingColor,
                offsetY: 12,
                formatter: val => parseInt(val)
              },
              total: {
                show: true,
                fontSize: '13px',
                color: legendColor,
                label: 'Total',
                formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              }
            }
          }
        }
      },
      states: {
        hover:  { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
      }
    }).render();
  }

  // ── 3. Leads by Source — Horizontal Bar Chart ────────────────────────────
  const leadsBySourceEl = document.querySelector('#leadsBySourceChart');
  if (leadsBySourceEl) {
    new ApexCharts(leadsBySourceEl, {
      series: [{
        name: 'Leads',
        data: data.leadsBySource?.data ?? []
      }],
      chart: {
        type: 'bar',
        height: 280,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          horizontal: true,
          barHeight: '55%',
          borderRadius: 4,
          borderRadiusApplication: 'end'
        }
      },
      colors: [config.colors.info],
      dataLabels: { enabled: false },
      grid: {
        strokeDashArray: 6,
        borderColor: borderColor,
        padding: { top: 0, bottom: -8 }
      },
      xaxis: {
        categories: data.leadsBySource?.labels ?? [],
        labels: {
          style: { fontSize: '13px', fontFamily, colors: labelColor }
        },
        axisTicks: { show: false },
        axisBorder: { show: false }
      },
      yaxis: {
        labels: {
          style: { fontSize: '13px', fontFamily, colors: labelColor }
        }
      },
      tooltip: {
        y: { formatter: val => val + ' leads' }
      },
      states: {
        hover:  { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
      }
    }).render();
  }

  // ── 4. Leads by Segment — Donut Chart ────────────────────────────────────
  const leadsBySegmentEl = document.querySelector('#leadsBySegmentChart');
  if (leadsBySegmentEl) {
    const segmentLabels = data.leadsBySegment?.labels ?? [];
    const segmentData   = data.leadsBySegment?.data   ?? [];

    new ApexCharts(leadsBySegmentEl, {
      series: segmentData,
      labels: segmentLabels,
      chart: {
        type: 'donut',
        height: 280
      },
      colors: [
        config.colors.success,
        config.colors.primary,
        config.colors.warning,
        config.colors.info,
        config.colors.danger,
        config.colors.secondary
      ],
      stroke: { width: 4, colors: [cardColor] },
      dataLabels: { enabled: false },
      legend: {
        show: true,
        position: 'bottom',
        fontFamily,
        fontSize: '13px',
        labels: { colors: legendColor }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '68%',
            labels: {
              show: true,
              name: {
                show: true,
                fontSize: '13px',
                fontFamily,
                color: legendColor,
                offsetY: -10
              },
              value: {
                show: true,
                fontSize: '1.125rem',
                fontFamily,
                fontWeight: 500,
                color: headingColor,
                offsetY: 12,
                formatter: val => parseInt(val)
              },
              total: {
                show: true,
                fontSize: '13px',
                color: legendColor,
                label: 'Total',
                formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              }
            }
          }
        }
      },
      states: {
        hover:  { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
      }
    }).render();
  }

  // ── 5. Top Vehicle Models — Bar Chart ────────────────────────────────────
  const topVehicleModelsEl = document.querySelector('#topVehicleModelsChart');
  if (topVehicleModelsEl) {
    new ApexCharts(topVehicleModelsEl, {
      series: [{
        name: 'Enquiries',
        data: data.topVehicleModels?.data ?? []
      }],
      chart: {
        type: 'bar',
        height: 280,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          columnWidth: '40%',
          borderRadius: 6,
          borderRadiusApplication: 'end',
          distributed: true
        }
      },
      colors: [
        config.colors.primary,
        config.colors.success,
        config.colors.warning,
        config.colors.info,
        config.colors.danger,
        config.colors.secondary,
        config.colors.primary
      ],
      dataLabels: { enabled: false },
      legend: { show: false },
      grid: {
        strokeDashArray: 6,
        borderColor: borderColor,
        padding: { top: 0, bottom: -8, left: 10, right: 10 }
      },
      xaxis: {
        categories: data.topVehicleModels?.labels ?? [],
        labels: {
          style: { fontSize: '13px', fontFamily, colors: labelColor }
        },
        axisTicks: { show: false },
        axisBorder: { show: false }
      },
      yaxis: {
        labels: {
          style: { fontSize: '13px', fontFamily, colors: labelColor }
        }
      },
      tooltip: {
        y: { formatter: val => val + ' enquiries' }
      },
      states: {
        hover:  { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
      }
    }).render();
  }
});
