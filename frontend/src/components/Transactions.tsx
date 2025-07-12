import React, { useState, useCallback } from 'react';
import { AgGridReact } from 'ag-grid-react';
import { ColDef, GridReadyEvent, CellValueChangedEvent } from 'ag-grid-community';
import 'ag-grid-community/styles/ag-grid.css';
import 'ag-grid-community/styles/ag-theme-alpine.css';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { transactionApi, fieldSettingApi } from '../services/api';
import { Transaction } from '../types';
import { useAuth } from '../contexts/AuthContext';
import DashboardLayout from './DashboardLayout';

const Transactions: React.FC = () => {
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const [searchText, setSearchText] = useState('');
  const [pagination, setPagination] = useState({
    page: 1,
    per_page: 50,
  });

  // Fetch transactions
  const { data: transactionsData, isLoading: transactionsLoading } = useQuery({
    queryKey: ['transactions', pagination, searchText],
    queryFn: () => transactionApi.getTransactions({
      ...pagination,
      search: searchText || undefined,
    }),
  });

  // Fetch field configuration
  const { data: fieldConfig, isLoading: configLoading } = useQuery({
    queryKey: ['fieldConfig'],
    queryFn: fieldSettingApi.getFieldConfig,
  });

  // Update transaction mutation
  const updateTransactionMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<Transaction> }) =>
      transactionApi.updateTransaction(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['transactions'] });
    },
  });

  const onCellValueChanged = useCallback((event: CellValueChangedEvent) => {
    const { data, colDef, newValue } = event;
    const fieldName = colDef.field!;
    
    // Check if user can edit this field
    if (user?.role !== 'admin' && user?.role !== 'editor') return;
    
    // Check if transaction is locked and user is not admin
    if (data.is_locked && user?.role !== 'admin') return;

    updateTransactionMutation.mutate({
      id: data.id,
      data: { [fieldName]: newValue },
    });
  }, [user, updateTransactionMutation]);

  const getColumnDefs = useCallback((): ColDef[] => {
    if (!fieldConfig) return [];

    const columns: ColDef[] = [
      {
        field: 'id',
        headerName: 'ID',
        width: 80,
        pinned: 'left',
        editable: false,
      },
    ];

    // Add imported fields
    fieldConfig.imported.forEach((field) => {
      let columnDef: ColDef = {
        field: field.name,
        headerName: field.name.replace('_', ' ').toUpperCase(),
        editable: false,
        width: 120,
      };

      if (field.name.includes('date')) {
        columnDef.valueFormatter = (params) => 
          params.value ? new Date(params.value).toLocaleDateString() : '';
      } else if (['amount', 'balance', 'specialist'].includes(field.name)) {
        columnDef.type = 'numericColumn';
        columnDef.valueFormatter = (params) => 
          params.value ? `$${Number(params.value).toFixed(2)}` : '$0.00';
      }

      columns.push(columnDef);
    });

    // Add manual fields
    fieldConfig.manual.forEach((field) => {
      let columnDef: ColDef = {
        field: field.name,
        headerName: field.name.replace('_', ' ').toUpperCase(),
        editable: (params) => {
          // Check user permissions
          if (user?.role === 'admin') return true;
          if (user?.role === 'editor' && !params.data.is_locked) return true;
          return false;
        },
        width: 120,
        cellClass: (params) => {
          if (params.data.is_locked) return 'locked-cell';
          if (user?.role === 'editor' || user?.role === 'admin') return 'editable-cell';
          return '';
        },
      };

      if (['registration', 'yearly', 'exam', 'certificate', 'newsletters', 'other', 'visa'].includes(field.name)) {
        columnDef.type = 'numericColumn';
        columnDef.valueFormatter = (params) => 
          params.value ? `$${Number(params.value).toFixed(2)}` : '$0.00';
        columnDef.valueParser = (params) => Number(params.newValue) || 0;
      }

      columns.push(columnDef);
    });

    // Add calculated fields
    fieldConfig.calculated.forEach((field) => {
      let columnDef: ColDef = {
        field: field.name,
        headerName: field.name.replace('_', ' ').toUpperCase(),
        editable: false,
        width: 120,
        cellClass: 'calculated-cell',
      };

      if (['unspecified', 'summary', 'commission', 'total', 'difference'].includes(field.name)) {
        columnDef.type = 'numericColumn';
        columnDef.valueFormatter = (params) => 
          params.value ? `$${Number(params.value).toFixed(2)}` : '$0.00';
      }

      columns.push(columnDef);
    });

    // Add status column
    columns.push({
      field: 'is_locked',
      headerName: 'STATUS',
      width: 100,
      editable: user?.role === 'admin',
      cellRenderer: (params: any) => (
        <span className={`px-2 py-1 text-xs rounded-full ${
          params.value 
            ? 'bg-red-100 text-red-800' 
            : 'bg-green-100 text-green-800'
        }`}>
          {params.value ? 'Locked' : 'Open'}
        </span>
      ),
    });

    return columns;
  }, [fieldConfig, user]);

  const onGridReady = (params: GridReadyEvent) => {
    params.api.sizeColumnsToFit();
  };

  if (transactionsLoading || configLoading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold text-gray-900">Transactions</h1>
          <div className="flex space-x-4">
            <input
              type="text"
              placeholder="Search transactions..."
              value={searchText}
              onChange={(e) => setSearchText(e.target.value)}
              className="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
        </div>

        {/* Grid */}
        <div className="bg-white rounded-lg shadow">
          <style>
            {`
              .editable-cell {
                background-color: #f0f9ff !important;
              }
              .locked-cell {
                background-color: #fef2f2 !important;
              }
              .calculated-cell {
                background-color: #f8fafc !important;
                font-weight: 600;
              }
            `}
          </style>
          <div className="ag-theme-alpine" style={{ height: '600px', width: '100%' }}>
            <AgGridReact
              rowData={transactionsData?.data || []}
              columnDefs={getColumnDefs()}
              onGridReady={onGridReady}
              onCellValueChanged={onCellValueChanged}
              pagination={true}
              paginationPageSize={pagination.per_page}
              suppressPaginationPanel={true}
              defaultColDef={{
                sortable: true,
                filter: true,
                resizable: true,
                minWidth: 100,
              }}
              rowSelection="multiple"
              enableRangeSelection={true}
              copyHeadersToClipboard={true}
              enableCellTextSelection={true}
            />
          </div>
          
          {/* Custom pagination */}
          {transactionsData && (
            <div className="px-4 py-3 border-t border-gray-200 sm:px-6">
              <div className="flex items-center justify-between">
                <div className="text-sm text-gray-700">
                  Showing {transactionsData.from} to {transactionsData.to} of{' '}
                  {transactionsData.total} results
                </div>
                <div className="flex space-x-2">
                  <button
                    disabled={transactionsData.current_page === 1}
                    onClick={() => setPagination(prev => ({ ...prev, page: prev.page - 1 }))}
                    className="px-3 py-1 text-sm border border-gray-300 rounded disabled:opacity-50"
                  >
                    Previous
                  </button>
                  <span className="px-3 py-1 text-sm">
                    Page {transactionsData.current_page} of {transactionsData.last_page}
                  </span>
                  <button
                    disabled={transactionsData.current_page === transactionsData.last_page}
                    onClick={() => setPagination(prev => ({ ...prev, page: prev.page + 1 }))}
                    className="px-3 py-1 text-sm border border-gray-300 rounded disabled:opacity-50"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
};

export default Transactions;
