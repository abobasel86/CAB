import React from 'react';
import { useQuery } from '@tanstack/react-query';
import { transactionApi } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import DashboardLayout from './DashboardLayout';
import {
  DocumentTextIcon,
  LockClosedIcon,
  LockOpenIcon,
  CurrencyDollarIcon,
} from '@heroicons/react/24/outline';

const Dashboard: React.FC = () => {
  const { user } = useAuth();

  // Fetch recent transactions for statistics
  const { data: transactionsData, isLoading } = useQuery({
    queryKey: ['transactions', { page: 1, per_page: 100 }],
    queryFn: () => transactionApi.getTransactions({ page: 1, per_page: 100 }),
  });

  const stats = React.useMemo(() => {
    if (!transactionsData?.data) return null;

    const transactions = transactionsData.data;
    const totalTransactions = transactions.length;
    const lockedTransactions = transactions.filter(t => t.is_locked).length;
    const openTransactions = totalTransactions - lockedTransactions;
    const totalAmount = transactions.reduce((sum, t) => sum + (t.amount || 0), 0);
    const totalCommission = transactions.reduce((sum, t) => sum + (t.commission || 0), 0);

    return {
      totalTransactions,
      lockedTransactions,
      openTransactions,
      totalAmount,
      totalCommission,
    };
  }, [transactionsData]);

  const statCards = [
    {
      name: 'Total Transactions',
      value: stats?.totalTransactions || 0,
      icon: DocumentTextIcon,
      color: 'bg-blue-500',
    },
    {
      name: 'Locked Transactions',
      value: stats?.lockedTransactions || 0,
      icon: LockClosedIcon,
      color: 'bg-red-500',
    },
    {
      name: 'Open Transactions',
      value: stats?.openTransactions || 0,
      icon: LockOpenIcon,
      color: 'bg-green-500',
    },
    {
      name: 'Total Amount',
      value: `$${(stats?.totalAmount || 0).toFixed(2)}`,
      icon: CurrencyDollarIcon,
      color: 'bg-purple-500',
    },
    {
      name: 'Total Commission',
      value: `$${(stats?.totalCommission || 0).toFixed(2)}`,
      icon: CurrencyDollarIcon,
      color: 'bg-indigo-500',
    },
  ];

  if (isLoading) {
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
        {/* Welcome Section */}
        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <h1 className="text-2xl font-bold text-gray-900">
              Welcome back, {user?.name}!
            </h1>
            <p className="mt-1 text-sm text-gray-600">
              You are logged in as <span className="font-medium">{user?.role}</span>
            </p>
          </div>
        </div>

        {/* Statistics Cards */}
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {statCards.map((stat) => (
            <div key={stat.name} className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <div className={`${stat.color} rounded-md p-3`}>
                      <stat.icon className="h-6 w-6 text-white" />
                    </div>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">
                        {stat.name}
                      </dt>
                      <dd className="text-lg font-medium text-gray-900">
                        {stat.value}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Recent Activity */}
        <div className="bg-white shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <h3 className="text-lg leading-6 font-medium text-gray-900">
              Recent Activity
            </h3>
            <div className="mt-5">
              {transactionsData?.data.slice(0, 5).map((transaction) => (
                <div key={transaction.id} className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center">
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-900">
                        {transaction.description || 'No description'}
                      </p>
                      <p className="text-sm text-gray-500">
                        {transaction.doctor_name} • {
                          transaction.post_date ? 
                          new Date(transaction.post_date).toLocaleDateString() : 
                          'No date'
                        }
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center">
                    <span className="text-sm font-medium text-gray-900">
                      ${(transaction.amount || 0).toFixed(2)}
                    </span>
                    <span className={`ml-2 px-2 py-1 text-xs rounded-full ${
                      transaction.is_locked 
                        ? 'bg-red-100 text-red-800' 
                        : 'bg-green-100 text-green-800'
                    }`}>
                      {transaction.is_locked ? 'Locked' : 'Open'}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="bg-white shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <h3 className="text-lg leading-6 font-medium text-gray-900">
              Quick Actions
            </h3>
            <div className="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              {(user?.role === 'admin' || user?.role === 'importer') && (
                <a
                  href="/import"
                  className="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium text-center"
                >
                  Import Transactions
                </a>
              )}
              <a
                href="/transactions"
                className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium text-center"
              >
                View Transactions
              </a>
              <a
                href="/export"
                className="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium text-center"
              >
                Export Data
              </a>
              {user?.role === 'admin' && (
                <a
                  href="/field-settings"
                  className="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium text-center"
                >
                  Field Settings
                </a>
              )}
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
};

export default Dashboard;
