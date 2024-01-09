using LOSPAÉ.ViewModels;

using Microsoft.UI.Xaml.Controls;

namespace LOSPAÉ.Views;

// TODO: Change the grid as appropriate for your app. Adjust the column definitions on DataGridPage.xaml.
// For more details, see the documentation at https://docs.microsoft.com/windows/communitytoolkit/controls/datagrid.
public sealed partial class NotesPage : Page
{
    public NotesViewModel ViewModel
    {
        get;
    }

    public NotesPage()
    {
        ViewModel = App.GetService<NotesViewModel>();
        InitializeComponent();
    }
}
