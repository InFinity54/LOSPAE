using System.Collections.ObjectModel;
using CommunityToolkit.Mvvm.ComponentModel;
using LOSPAÉ.Contracts.ViewModels;

namespace LOSPAÉ.ViewModels;

public partial class HistoryViewModel : ObservableRecipient, INavigationAware
{
    public ObservableCollection<NoteEditEvent> Source { get; } = new ObservableCollection<NoteEditEvent>();

    public async void OnNavigatedTo(object parameter)
    {
        Source.Clear();

        foreach (var item in App.noteEditEvents.OrderByDescending(x => x.ChangedOccurDateTime))
        {
            Source.Add(item);
        }

        Source.OrderByDescending(x => x.ChangedOccurDateTime);
    }

    public void OnNavigatedFrom()
    {
    }
}
